<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\emails\emails;
use App\Helpers\processClient;
use App\Helpers\processTransactions;
use App\Models\sales\Transactions;
use Illuminate\Http\Request;
use App\Services\PaymentBacService;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Models\sales\PaymentTransactions;
use App\Models\sales\ResponseCode;
use App\Models\sales\TransactionFloat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BacController extends Controller
{
    public function auth(Request $request)
    {
        try {
            $ip = $request->ip() ?? '192.168.1.1';
            $clientSales = new processClient();
            $client = $clientSales->createClient($request);
            $total_amount_floating = $request->input('total_amount_floating');

            if (!processTransactions::validateTotal($request)) {
                Log::error('El monto total no coincide con el detalle de la compra');
                return response()->json(['error' => true, 'code' => 400, 'message' => 'El monto total no coincide con el detalle de la compra'], 400);
            }

            $processTransactions = new processTransactions();
            $transactions = $processTransactions->crateTransactions($request, $client, $ip, 'bac');

            $id_order     = $request->input('order_number');
            $total_amount = $request->input('total_amount');
            $currency     = $request->input('currency');

            $BillingAddress = [
                'FirstName'    => $client->first_name,
                'LastName'     => $client->last_name,
                'EmailAddress' => $client->email,
                'PhoneNumber'  => $client->phone,
            ];

            $source = [
                'CardPan'        => $request->card_payment['number_card'],
                'CardCvv'        => $request->card_payment['cvv_card'],
                'CardExpiration' => $request->card_payment['expiration_year'] . $request->card_payment['expiration_month'],
                'CardholderName' => $client->name,
            ];

            $data = [
                'TotalAmount'     => $total_amount,
                'CurrencyCode'    => $currency,
                'ThreeDSecure'    => $request->input('three_ds', false) ? true : false,
                'Source'          => $source,
                'OrderIdentifier' => $id_order,
                'BillingAddress'  => $BillingAddress,
            ];

            // --- PASO 1: Auth SPI ---
            $response_auth = PaymentBacService::processAuth($data);
            $auth_data     = $response_auth['data'] ?? [];
            $auth_iso      = $auth_data['IsoResponseCode'] ?? '';

            // Auth fallido (no SP4 ni 00) → cortar aquí, no intentar Capture
            if ($response_auth['Code'] !== 200 || in_array($auth_iso, ['97', '05', '12'])) {
                $transactions->fill([
                    'request_id'         => $auth_data['TransactionIdentifier'] ?? '',
                    'request_status'     => 'DECLINED',
                    'request_code'       => $auth_iso,
                    'status_transaction' => 'Auth',
                ]);
                $transactions->save();
                return $this->respondError($auth_data, $transactions);
            }

            // SP4 → el usuario debe autenticarse en el iFrame (challenge o frictionless con fingerprint)
            if ($auth_iso === 'SP4' && !empty($auth_data['RedirectData'])) {

                // Guardamos SpiToken cifrado para recuperarlo en handle() cuando PowerTranz
                // haga POST a MerchantResponseUrl con el resultado de la autenticación
                PaymentTransactions::create([
                    'uuid'                   => Str::uuid(),
                    'transaction_uuid'       => $transactions->uuid,
                    'transaction_type'       => 'Auth',
                    'approved'               => false,
                    'transaction_identifier' => $auth_data['TransactionIdentifier'] ?? '',
                    'order_identifier'       => $id_order,
                    'total_amount'           => $total_amount,
                    'currency_code'          => $currency,
                    'iso_response_code'      => $auth_iso,
                    'spi_token_encrypted'    => Crypt::encryptString($auth_data['SpiToken'] ?? ''),
                    'external_identifier'    => $transactions->uuid,
                ]);

                $transactions->fill([
                    'request_id'         => $auth_data['TransactionIdentifier'] ?? '',
                    'request_status'     => 'PENDING_3DS',
                    'request_code'       => $auth_iso,
                    'status_transaction' => 'Auth',
                ]);
                $transactions->save();

                // El frontend debe insertar redirect_data en un iFrame:
                // <iframe srcdoc="{redirect_data}" width="100%" height="500"></iframe>
                return response()->json([
                    'error'            => false,
                    'code'             => 202,
                    'requires_3ds'     => true,
                    'redirect_data'    => $auth_data['RedirectData'],
                    'transaction_uuid' => $transactions->uuid,
                    'message'          => 'Inserta redirect_data en un iFrame para completar la autenticación 3DS.',
                ], 202);
            }

            // Frictionless directo (IsoResponseCode 00, Approved true) → Capture → Payment
            if ($auth_iso === '00' && ($auth_data['Approved'] ?? false)) {
                $capture_args = [
                    'data_capture' => [
                        'TotalAmount'           => $total_amount,
                        'TransactionIdentifier' => $auth_data['TransactionIdentifier'],
                    ],
                    'transaction_uuid' => $transactions->uuid,
                    'pay'              => true,
                    'is_floating'      => false,
                    'spi_token'        => $auth_data['SpiToken'] ?? '',
                ];

                $response = $this->processCapture($capture_args);
                return $this->buildFinalResponse($response, $transactions, $client, $data, $total_amount_floating);
            }

            return $this->respondError($auth_data, $transactions);

        } catch (\Exception $e) {
            Log::error('BacController@auth exception', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['error' => true, 'code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /payment/bac/response  ← MerchantResponseUrl configurada en PowerTranz
     *
     * PowerTranz hace POST aquí con el resultado del challenge 3DS.
     * Completa el pago con el SpiToken y responde con postMessage al iFrame padre.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('BAC 3DS Callback recibido', ['payload' => $payload]);

        // PowerTranz envía el resultado dentro del campo "Response" como string JSON
        $response_data = [];
        if (!empty($payload['Response']) && is_string($payload['Response'])) {
            $response_data = json_decode($payload['Response'], true) ?? [];
        }

        try {
            $isoCode       = $response_data['IsoResponseCode']      ?? $payload['IsoResponseCode']      ?? null;
            $spiToken      = $response_data['SpiToken']              ?? $payload['SpiToken']              ?? null;
            $transactionId = $response_data['TransactionIdentifier'] ?? $payload['TransactionIdentifier'] ?? null;

            Log::info('BAC handle parsed', compact('isoCode', 'transactionId'));

            $paymentTransaction = PaymentTransactions::where('transaction_identifier', $transactionId)
                ->where('transaction_type', 'Auth')
                ->where('approved', false)
                ->first();

            if (!$paymentTransaction) {
                Log::error('BAC handle: PaymentTransaction no encontrado', ['transaction_id' => $transactionId]);
                return $this->redirectFrontend('error', 'Transacción no encontrada');
            }

            $transactionUuid = $paymentTransaction->external_identifier;
            $transactions    = Transactions::where('uuid', $transactionUuid)->first();

            // 3DS fallido — no intentar Payment
            if (in_array($isoCode, ['3D1', '3D2', 'SP6', 'RP'])) {
                Log::warning('BAC handle: 3DS fallido', ['iso_code' => $isoCode]);
                if ($transactions) {
                    $transactions->fill(['request_status' => 'DECLINED', 'request_code' => $isoCode]);
                    $transactions->save();
                }
                return $this->redirectFrontend('error', 'Autenticación 3DS fallida', $transactionUuid);
            }

            // Si PowerTranz no envió el SpiToken en el callback, usar el que guardamos cifrado
            if (empty($spiToken)) {
                $encrypted = $paymentTransaction->spi_token_encrypted ?? null;
                $spiToken  = $encrypted ? Crypt::decryptString($encrypted) : null;
            }

            if (empty($spiToken)) {
                Log::error('BAC handle: SpiToken no disponible');
                return $this->redirectFrontend('error', 'SpiToken no disponible', $transactionUuid);
            }

            // --- PASO 2: completar el pago con el SpiToken ---
            $response_payment = PaymentBacService::processPayment(['SpiToken' => $spiToken]);
            $data             = $response_payment['data'] ?? [];
            $approved         = $data['Approved'] ?? false;

            Log::info('BAC handle: resultado de Payment', ['response' => $response_payment]);

            $paymentTransaction->update([
                'transaction_type'    => $this->getStatus($data['TransactionType'] ?? 2),
                'approved'            => $approved,
                'authorization_code'  => $data['AuthorizationCode'] ?? '',
                'iso_response_code'   => $data['IsoResponseCode'] ?? '',
                'pan_token'           => $data['PanToken'] ?? '',
                'spi_token_encrypted' => '',
            ]);

            if ($transactions) {
                $transactions->fill([
                    'request_id'         => $data['TransactionIdentifier'] ?? $transactionId,
                    'request_status'     => $approved ? 'APPROVED' : 'DECLINED',
                    'request_code'       => $data['IsoResponseCode'] ?? '',
                    'request_auth'       => $data['AuthorizationCode'] ?? '',
                    'status_transaction' => $this->getStatus($data['TransactionType'] ?? 2),
                ]);
                $transactions->save();

                if ($approved) {
                    try {
                        $client = (object) [
                            'first_name' => $paymentTransaction->billingAddress['FirstName'] ?? '',
                            'last_name'  => $paymentTransaction->billingAddress['LastName'] ?? '',
                            'email'      => $paymentTransaction->billingAddress['EmailAddress'] ?? '',
                            'name'       => trim(($paymentTransaction->billingAddress['FirstName'] ?? '') . ' ' . ($paymentTransaction->billingAddress['LastName'] ?? '')),
                            'phone'      => $paymentTransaction->billingAddress['PhoneNumber'] ?? '',
                        ];
                        emails::sendEmailPaymentAccept($client, $transactions);
                    } catch (\Exception $emailEx) {
                        Log::warning('No se pudo enviar email post-3DS', ['error' => $emailEx->getMessage()]);
                    }
                }
            }

            return $this->redirectFrontend(
                $approved ? 'success' : 'error',
                $approved ? 'Pago aprobado' : ($data['ResponseMessage'] ?? 'Pago rechazado'),
                $transactionUuid,
                $data['TransactionIdentifier'] ?? $transactionId
            );

        } catch (\Exception $e) {
            Log::error('BAC handle exception', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            return $this->redirectFrontend('error', 'Error interno');
        }
    }

    // -------------------------------------------------------------------------
    // ENDPOINTS ADICIONALES
    // -------------------------------------------------------------------------

    public function capture(Request $request)
    {
        $float_transaction_uuid = $request->input('float_transaction_uuid');
        $total_amount           = $request->input('TotalAmount');
        $pay                    = $request->input('pay');

        $float_transaction = TransactionFloat::where('uuid', $float_transaction_uuid)->first();
        if (!$float_transaction) return response()->json(['message' => 'Transacción no encontrada'], 404);
        if ($total_amount <= 0) return response()->json(['message' => 'El monto debe ser mayor que cero'], 400);

        $payments_captured = PaymentTransactions::where('transaction_uuid', $float_transaction->transaction_uuid)
            ->whereNot('transaction_type', 'Refund')->sum('total_amount');

        if (($payments_captured + $total_amount) > $float_transaction->total) {
            return response()->json(['message' => 'El monto excede el autorizado'], 400);
        }

        $args = [
            'data_capture'     => ['TotalAmount' => $total_amount, 'TransactionIdentifier' => $float_transaction->request_id],
            'transaction_uuid' => $float_transaction->transaction_uuid,
            'pay'              => $pay,
            'is_floating'      => true,
        ];

        $result = $this->processCapture($args);
        return response()->json([
            'message'          => 'Procesando captura',
            'Approved'         => $result['data']['Approved'],
            'transaction_uuid' => $float_transaction->transaction_uuid,
        ], $result['Code']);
    }

    public function refund(Request $request)
    {
        $capture_uuid       = $request->input('capture_uuid');
        $paymentTransaction = PaymentTransactions::where('uuid', $capture_uuid)->where('transaction_type', 'Capture')->first();
        if (!$paymentTransaction) return response()->json(['message' => 'Captura no encontrada'], 404);

        $data = [
            'Refund'                => true,
            'TransactionIdentifier' => $paymentTransaction->transaction_identifier,
            'TotalAmount'           => $paymentTransaction->total_amount,
        ];

        $response      = PaymentBacService::processRefund($data);
        $data_response = $response['data'];

        $paymentTransaction->update([
            'refund_id'           => $data_response['TransactionIdentifier'] ?? '',
            'date_refund'         => now(),
            'transaction_type'    => $this->getStatus($data_response['TransactionType'] ?? 5),
            'spi_token_encrypted' => '',
        ]);

        return response()->json(['message' => 'Procesando reembolso', 'data' => $data_response], $response['Code']);
    }

    public function void(Request $request)
    {
        $float_transaction_uuid = $request->input('float_transaction_uuid');
        $float_transaction      = TransactionFloat::where('uuid', $float_transaction_uuid)->first();
        if (!$float_transaction) return response()->json(['message' => 'Transacción no encontrada'], 404);

        $response = PaymentBacService::processVoid([
            'ExternalIdentifier'    => '',
            'TransactionIdentifier' => $float_transaction->request_id,
        ]);

        $float_transaction->update([
            'refund_id'   => $response['data']['TransactionIdentifier'] ?? '',
            'date_refund' => now(),
        ]);

        return response()->json(['message' => 'Procesando anulación', 'data' => $response['data']], $response['Code']);
    }

    public function payment(Request $request)
    {
        $capture_uuid       = $request->input('capture_uuid');
        $paymentTransaction = PaymentTransactions::where('uuid', $capture_uuid)->first();
        if (!$paymentTransaction) return response()->json(['message' => 'Transacción no encontrada'], 404);

        $spiToken = Crypt::decryptString($paymentTransaction->spi_token_encrypted);
        if (!$spiToken) return response()->json(['message' => 'SpiToken no encontrado'], 404);

        $response = $this->processPay($spiToken, $paymentTransaction);
        return response()->json(['message' => 'Procesando pago', 'data' => $response['data']], $response['Code']);
    }

    public function transactions(Request $request)
    {
        $transaction_uuid = $request->input('transaction_uuid');
        $data = Transactions::with('captures')->where('uuid', $transaction_uuid)->get();
        return response()->json(['message' => 'Transaccion', 'data' => $data], 200);
    }

    public function alive()
    {
        $response = PaymentBacService::processAlive();
        return $response['Code'] === 200
            ? response()->json(['message' => 'La API de BAC está viva'], 200)
            : response()->json(['message' => 'La API de BAC no está disponible'], 503);
    }

    // -------------------------------------------------------------------------
    // MÉTODOS PRIVADOS
    // -------------------------------------------------------------------------

    private function processCapture(array $args): array
    {
        $is_three_ds      = (bool) config('services.bac.is_three_ds', false);
        $data             = $args['data_capture'];
        $total_amount     = $data['TotalAmount'];
        $transaction_uuid = $args['transaction_uuid'];
        $pay              = $args['pay'] ?? false;
        $is_floating      = $args['is_floating'] ?? true;
        $spiToken         = $args['spi_token'] ?? '';
        $capture_data     = null;

        $response_capture = PaymentBacService::processCapture($data);
        $data_response    = $response_capture['data'] ?? [];

        Log::info('Respuesta de captura', ['response' => $data_response]);

        if ($is_floating) {
            $capture_data = PaymentTransactions::create([
                'uuid'                     => Str::uuid(),
                'transaction_uuid'         => $transaction_uuid,
                'original_trxn_identifier' => $data_response['OriginalTrxnIdentifier'] ?? '',
                'transaction_type'         => $this->getStatus($data_response['TransactionType'] ?? 3),
                'approved'                 => $data_response['Approved'] ?? false,
                'authorization_code'       => $data_response['AuthorizationCode'] ?? '',
                'transaction_identifier'   => $data_response['TransactionIdentifier'] ?? '',
                'total_amount'             => $total_amount,
                'currency_code'            => $data_response['CurrencyCode'] ?? '',
                'rrn'                      => $data_response['RRN'] ?? '',
                'host_rrn'                 => $data_response['HostRRN'] ?? '',
                'card_brand'               => $data_response['CardBrand'] ?? '',
                'card_suffix'              => $data_response['CardSuffix'] ?? '',
                'iso_response_code'        => $data_response['IsoResponseCode'] ?? '',
                'pan_token'                => $data_response['PanToken'] ?? '',
                'external_identifier'      => $data_response['ExternalIdentifier'] ?? '',
                'order_identifier'         => $data_response['OrderIdentifier'] ?? '',
                'spi_token_encrypted'      => $spiToken ? Crypt::encryptString($spiToken) : '',
            ]);
        }

        if ($pay && $is_three_ds && !empty($spiToken)) {
            return $this->processPay($spiToken, $capture_data);
        }

        return $response_capture;
    }

    private function processPay(string $spiToken, $paymentTransaction): array
    {
        $response      = PaymentBacService::processPayment(['SpiToken' => $spiToken]);
        $data_response = $response['data'] ?? [];

        if (!empty($paymentTransaction)) {
            $paymentTransaction->update([
                'spi_token_encrypted' => '',
                'transaction_type'    => $this->getStatus($data_response['TransactionType'] ?? 2),
                'approved'            => $data_response['Approved'] ?? false,
                'authorization_code'  => $data_response['AuthorizationCode'] ?? '',
                'iso_response_code'   => $data_response['IsoResponseCode'] ?? '',
            ]);
        }

        return $response;
    }

    private function processFloating(array $args): array
    {
        $data             = $args['data'];
        $transaction_uuid = $args['transaction_uuid'];

        $float_transaction = TransactionFloat::create([
            'uuid'             => Str::uuid(),
            'transaction_uuid' => $transaction_uuid,
            'total'            => $data['TotalAmount'],
            'request_id'       => '',
        ]);

        $data['OrderIdentifier'] = str_replace('-', '', $float_transaction->uuid);
        $response_auth = PaymentBacService::processAuth($data);

        $float_transaction->update(['request_id' => $response_auth['data']['TransactionIdentifier'] ?? '']);
        Log::info('processFloating completado', ['response' => $response_auth]);
        return $response_auth;
    }

    private function buildFinalResponse(array $response, $transactions, $client, array $data, $total_amount_floating): \Illuminate\Http\JsonResponse
    {
        $approved = $response['data']['Approved'] ?? false;

        $transactions->fill([
            'request_id'         => $response['data']['TransactionIdentifier'] ?? '',
            'request_status'     => $approved ? 'APPROVED' : 'DECLINED',
            'request_code'       => $response['data']['IsoResponseCode'] ?? '',
            'request_auth'       => $response['data']['AuthorizationCode'] ?? '',
            'status_transaction' => $this->getStatus($response['data']['TransactionType'] ?? 0),
        ]);
        $transactions->save();

        if ($approved) {
            // emails::sendEmailPaymentAccept($client, $transactions);

            if ($total_amount_floating > 0) {
                $data_floating                = $data;
                $data_floating['TotalAmount'] = $total_amount_floating;
                $this->processFloating(['data' => $data_floating, 'transaction_uuid' => $transactions->uuid]);
            }

            $dateTransaction = $transactions->date_transaction;
            $transaction     = Transactions::where('uuid', $transactions->uuid)->first();

            return response()->json([
                'error' => false,
                'code'  => 200,
                'data'  => [
                    'url_voucher'  => $transaction->url_voucher ?? '',
                    'data_voucher' => [
                        'request_id'        => $transactions->request_id,
                        'code_payment'      => $transactions->identifier_payment,
                        'date_transaction'  => Carbon::parse($dateTransaction)->format('d-m-Y'),
                        'hour_transactions' => Carbon::parse($dateTransaction)->format('g:i A'),
                        'last_card'         => $transactions->value_payment,
                        'total'             => $transactions->total,
                        'uuid_transaction'  => $transactions->uuid,
                    ],
                    'decision'    => 'ACCEPT',
                    'reasonCode'  => $response['data']['IsoResponseCode'] ?? '',
                    'requestID'   => $response['data']['TransactionIdentifier'] ?? '',
                    'transactions'=> $transactions->uuid,
                ],
            ], 200);
        }

        return $this->respondError($response['data'], $transactions);
    }

    private function respondError(array $data, $transactions): \Illuminate\Http\JsonResponse
    {
        $isoCode = $data['IsoResponseCode'] ?? '';

        $code = ResponseCode::where('code', $isoCode)
            ->where('code_payment', $transactions->identifier_payment ?? '')
            ->where('language', 'ES')
            ->select('code', 'code_payment', 'language', 'description', 'message')
            ->first();

        return response()->json([
            'error' => true,
            'code'  => 400,
            'data'  => [
                'decision'          => 'REJECT',
                'reasonCode'        => $isoCode ?: ($data['ResponseMessage'] ?? ''),
                'requestID'         => $data['TransactionIdentifier'] ?? '',
                'authorizationCode' => $data['AuthorizationCode'] ?? '',
                'error_code'        => $code,
                'errors'            => $data['Errors'] ?? [],
            ],
        ], 400);
    }

    private function redirectFrontend(
        string $status,
        string $message,
        ?string $transactionUuid = null,
        ?string $requestId = null
    ) {
        $approved = $status === 'success';
        return response(
            '<script>
                window.parent.postMessage({
                    type: "ptranz_result",
                    approved: ' . ($approved ? 'true' : 'false') . ',
                    status: "' . $status . '",
                    message: "' . addslashes($message) . '",
                    transaction_uuid: "' . ($transactionUuid ?? '') . '",
                    request_id: "' . ($requestId ?? '') . '"
                }, "*");
            </script>',
            200,
            ['Content-Type' => 'text/html']
        );
    }

    private function getStatus(int $status): string
    {
        return [
            1 => 'Auth',
            2 => 'Sale',
            3 => 'Capture',
            4 => 'Void',
            5 => 'Refund',
            6 => 'Credit',
        ][$status] ?? 'Unknown';
    }
}
