<?php

namespace App\Http\Controllers\Payment;

use App\Models\sales\Transactions;
use Illuminate\Http\Request;
use App\Services\PaymentBacService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Models\sales\PaymentTransactions;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BacController extends Controller
{
    public function auth(Request $request)
    {
        $id_order = $request->input('OrderIdentifier');
        $total_amount = $request->input('TotalAmount');
        $currency_code = $request->input('CurrencyCode');
        $BillingAddress = $request->input('BillingAddress', null);
        $source = $request->input('Source');
        $last_four = substr($source['Card']['AccountNumber'] ?? '', -4);

        $data = [
            'TotalAmount'       => $total_amount, //null, //
            'CurrencyCode'      => $currency_code,
            'ThreeDSecure'      => false,
            'Source'            => $source,
            'OrderIdentifier'   => $id_order,
            'BillingAddress'    => $BillingAddress,
            'ShippingAddress'   => $request->input('ShippingAddress', null),
        ];

        $transactions =  Transactions::create(
            [
                'uuid'                  => Str::uuid(),
                'id_order'              => $id_order,
                'client_name'           => ($BillingAddress['FirstName'] ?? '')  . ' ' . ($BillingAddress['LastName'] ?? ''),
                'client_uuid'           => fake()->uuid(),
                'ip_location'           => fake()->ipv4(),
                'device_id'             => '',
                'country'               => 'GT',
                'currency'              => $currency_code,
                'total'                 => $total_amount,
                'date_transaction'      => Carbon::now(),
                'request_id'            => '',
                'request_status'        => '',
                'request_code'          => '',
                'request_auth'          => '',
                'status_transaction'    => '',
                'payment'               => "Bac",
                'identifier_payment'    => "BAC",
                'value_payment'         => $last_four,
                'type_card'             => 'visa'
            ]
        );
        
        $response = PaymentBacService::processAuth($data);

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

        return response()->json([
            'message' => 'Procesando autenticación de pago',
            'data' => $response['data'],
            'transaction_uuid' => $transactions->uuid,
        ], $response['Code']);
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

    private function getStatus(int $status): string{
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
