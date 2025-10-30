<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\emails\emails;
use App\Helpers\processClient;
use App\Helpers\processTransactions;
use App\Models\sales\Transactions;
use Illuminate\Http\Request;
use App\Services\PaymentBacService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Models\sales\PaymentMethod;
use App\Models\sales\PaymentTransactions;
use App\Models\sales\ResponseCode;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BacController extends Controller
{
    public function auth(Request $request)
    {
        try {
            $ip = $request->ip() ?? '192.168.1.1';
            $clientSales = new processClient();
            $client = $clientSales->createClient($request);

            // $methodBusiness = PaymentMethod::where('payment_code','BAC')->where('currency',$request->currency)->where('status',1)->first();
            // if (empty($methodBusiness))
            // {
            //     return response()->json(['error' => 'true', 'code' => 400, 'message' =>"No cuentas con credenciales en: ".$request->currency],400);
            // }
            // $credentials = json_decode($methodBusiness->credentials,true);
            // $function = json_decode($methodBusiness->function,true);

            $processTransactions = new processTransactions();
            $transactions = $processTransactions->crateTransactions($request, $client, $ip, 'bac');

            $id_order = $request->input('order_number');
            $total_amount = $request->input('total_amount');
            $currency_code = $request->input('currency');
            // $BillingAddress = $request->input('BillingAddress', null);
            // $source = $request->input('Source');
            $BillingAddress = [
                'FirstName'     => $client->first_name,
                'LastName'      => $client->last_name,
                'EmailAddress'  => $client->email,
                'PhoneNumber'   => $client->phone,
            ];

            $source = [
                'CardPan'           => $request->card_payment['number_card'],
                'CardCvv'           => $request->card_payment['cvv_card'],
                'CardExpiration'    => $request->card_payment['expiration_year'] . $request->card_payment['expiration_month'],
                'CardholderName'    => $client->name,
            ];

            $data = [
                'TotalAmount'       => $total_amount,
                'CurrencyCode'      => $currency_code,
                'ThreeDSecure'      => false,
                'Source'            => $source,
                'OrderIdentifier'   => $id_order,
                'BillingAddress'    => $BillingAddress,
                // 'ShippingAddress'   => $request->input('ShippingAddress', null),
            ];

            $response = PaymentBacService::processAuth($data);
            
            $status = isset($response['data']['Approved']) ? $response['data']['Approved'] : false;
            if ($status) {
                $transactions->fill(
                    [
                        'request_id'            => $response['data']['TransactionIdentifier'] ?? '',
                        'request_status'        => $response['data']['Approved'] ? 'APPROVED' : 'DECLINED',
                        'request_code'          => $response['data']['IsoResponseCode'] ?? '',
                        'request_auth'          => $response['data']['AuthorizationCode'] ?? '',
                        'status_transaction'    => $this->getStatus($response['data']['TransactionType']),
                    ]
                );
                $transactions->save();

                emails::sendEmailPaymentAccept($client, $transactions);

                $dateTransaction = $transactions->date_transaction;
                $date = Carbon::parse($dateTransaction)->format('d-m-Y');
                $hour =  $dateTransaction->format('g:i A');
                $dataVoucher = [
                    // 'merchant'=>    $methodBusiness->merchant,
                    'request_id' => $transactions->request_id,
                    'code_payment' => $transactions->identifier_payment,
                    'date_transaction' => $date,
                    'hour_transactions' => $hour,
                    'last_card' => $transactions->value_payment,
                    'total' => $transactions->total,
                    'uuid_transaction' => $transactions->uuid,
                ];

                $transaction = Transactions::where('uuid', $transactions->uuid)->first();
                $objData = [
                    'url_voucher'       => $transaction->url_voucher,
                    'data_voucher'      => $dataVoucher,
                    'decision'          =>  $response['data']['Approved'] ? 'ACCEPT' : 'REJECT',
                    'reasonCode'        =>  $response['data']['IsoResponseCode'] ?? $response['data']['ResponseMessage'],
                    'requestID'         =>  $response['data']['TransactionIdentifier'],
                    'transactions' =>  $transactions->uuid
                ];
                return response()->json(['code' => 200, 'error' => false, 'data' => $objData], 200);
            } else {
                $transactions->fill(
                    [
                        'request_id'            => $transactions->request_id,
                        'request_status'        => $response['data']['Approved'] ? 'APPROVED' : 'DECLINED',
                        'request_code'          => $response['data']['IsoResponseCode'] ?? '',
                        'request_auth'          => $response['data']['AuthorizationCode'] ?? '',
                        'status_transaction'    => $this->getStatus($response['data']['TransactionType']),
                    ]
                );
                $transactions->save();

                $code = ResponseCode::where('code', $response['data']['IsoResponseCode'])->where('code_payment', $transactions->identifier_payment)
                    ->where('language', 'ES')
                    ->select(
                        'code',
                        'code_payment',
                        'language',
                        'description',
                        'message'
                    )
                    ->first();

                $data = [
                    'decision'          =>  $response['data']['Approved'] ? 'ACCEPT' : 'REJECT',
                    'reasonCode'        =>  $response['data']['IsoResponseCode'] ?? $response['data']['ResponseMessage'],
                    'requestID'         =>  $response['data']['TransactionIdentifier'],
                    'authorizationCode' =>  $response['data']['AuthorizationCode'],
                    'error_code'      => $code,
                ];

                return response()->json(['error' => true, 'code' => 400, 'data' => $data], 400);
            }
        } catch (\Exception $e) {
            $decision = $e->getMessage();
            return response()->json(['error' => 'true', 'code' => 400, 'message' => $decision], 400);
        }
    }

    public function capture(Request $request)
    {
        $transaction_uuid = $request->input('transaction_uuid');
        $total_amount = $request->input('TotalAmount');

        $transaction = Transactions::where('uuid', $transaction_uuid)->first();
        if (!$transaction) {
            return response()->json(['message' => 'Transacción no encontrada'], 404);
        }

        if ($total_amount <= 0) {
            return response()->json(['message' => 'El monto a capturar debe ser mayor que cero'], 400);
        }

        $data = [
            'TotalAmount'       => $total_amount,
            'TransactionIdentifier'      => $transaction->request_id,
        ];

        $payments_captured = PaymentTransactions::where('transaction_uuid', $transaction_uuid)->whereNot('transaction_type', 'Refund')->sum('total_amount');
        if (($payments_captured + $total_amount) > $transaction->total) {
            return response()->json(['message' => 'El monto a capturar excede el monto autorizado'], 400);
        }

        $response = PaymentBacService::processCapture($data);
        $data_response = $response['data'];

        $capture_data = PaymentTransactions::create([
            'uuid' => Str()->uuid(),
            'transaction_uuid' => $transaction_uuid,
            'original_trxn_identifier' => $data_response['OriginalTrxnIdentifier'],
            'transaction_type' => $this->getStatus($data_response['TransactionType']),
            'approved' => $data_response['Approved'],
            'authorization_code' => $data_response['AuthorizationCode'],

            'transaction_identifier' => $data_response['TransactionIdentifier'],
            'total_amount' => $total_amount,
            'currency_code' => $data_response['CurrencyCode'],
            'rrn' => $data_response['RRN'],
            'host_rrn' => $data_response['HostRRN'],
            'card_brand' => $data_response['CardBrand'],
            'card_suffix' => $data_response['CardSuffix'],
            'iso_response_code' => $data_response['IsoResponseCode'],
            'pan_token' => $data_response['PanToken'],
            'external_identifier' => $data_response['ExternalIdentifier'],
            'order_identifier' => $data_response['OrderIdentifier'],
            'spi_token_encrypted' => Crypt::encryptString($data_response['SpiToken']),
        ]);

        return response()->json([
            'message' => 'Procesando captura de pago',
            'Approved' => $response['data']['Approved'],
            'transaction_uuid' => $transaction_uuid,
        ], $response['Code']);
    }

    public function refund(Request $request)
    {
        $capture_uuid = $request->input('capture_uuid');

        $paymentTransaction = PaymentTransactions::where('uuid', $capture_uuid)->where('transaction_type', 'Capture')->first();
        if (!$paymentTransaction) {
            return response()->json(['message' => 'Captura de Transacción no encontrada'], 404);
        }

        $data = [
            'Refund'                    => true,
            'TransactionIdentifier'     => $paymentTransaction->transaction_identifier,
            'TotalAmount'               => $paymentTransaction->total_amount,
            // 'TipAmount'                 => $request->input('TipAmount'),
            // 'TaxAmount'                 => $request->input('TaxAmount'),
        ];

        $response = PaymentBacService::processRefund($data);
        $data_response = $response['data'];

        $paymentTransaction->update([
            'refund_id' => $response['data']['TransactionIdentifier'],
            'date_refund' => now(),
            'transaction_type' => $this->getStatus($data_response['TransactionType']),
            'spi_token_encrypted' => '', // Limpiar el SpiToken almacenado
        ]);

        return response()->json(['message' => 'Procesando reembolso', 'data' => $response['data']], $response['Code']);
    }

    public function payment(Request $request)
    {
        $capture_uuid = $request->input('capture_uuid');

        $paymentTransaction = PaymentTransactions::where('uuid', $capture_uuid)->first();
        if (!$paymentTransaction) {
            return response()->json(['message' => 'Captura de Transacción no encontrada'], 404);
        }

        $spiToken = Crypt::decryptString($paymentTransaction->spi_token_encrypted);
        if (!$spiToken) {
            return response()->json(['message' => 'SpiToken no encontrado'], 404);
        }

        $data = [
            'SpiToken' => $spiToken,
        ];

        $response = PaymentBacService::processPayment($data);
        $data_response = $response['data'];

        $paymentTransaction->update([
            'spi_token_encrypted' => '', // Limpiar el SpiToken almacenado
            'transaction_type' => $this->getStatus($data_response['TransactionType']),
        ]);

        return response()->json([
            'message' => 'Procesando pago',
            'data' => $response['data']
        ], $response['Code']);
    }

    public function transactions(Request $request)
    {
        $transaction_uuid = $request->input('transaction_uuid');
        $data = Transactions::with('captures')->where('uuid', $transaction_uuid)->get();
        return response()->json(['message' => 'Transaccion', 'data' => $data], 200);
    }

    private function getStatus(int $status): string
    {
        $arr_status = [
            1 => 'Auth',
            2 => 'Sale',
            3 => 'Capture',
            4 => 'Void',
            5 => 'Refund',
            6 => 'Credit',
        ];
        return $arr_status[$status] ?? 'Unknown';
    }
}
