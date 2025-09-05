<?php
namespace App\Helpers;

use App\Models\sales\DetailTransactions;
use App\Models\sales\Transactions;
use http\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class processTransactions{

    public static function crateTransactions($request,$client,$ip){
        $accountNumber = $request->card_payment['number_card'];
        $solo4           = substr($accountNumber, -4);
        $typeCard = validateCard::check_cc($accountNumber);
        $amount = $request->total_amount;
        $product = $request->detail;


        // todo Se crea la transaccion

        $transactions =  Transactions::create(
            [
                'uuid'                  => Str::uuid(),
                'id_order'              => $request->order_number ?? '',
//                    'user_uuid'             => '',
                'client_name'           => $client->name,
                'client_uuid'           => $client->uuid,
                'ip_location'           => $ip,
                'device_id'             => $request->deviceFinger,
                'country'               => 'GT',
                'currency'              => $request->currency,
                'total'                 => $amount,
                'date_transaction'      => Carbon::now(),
                'request_id'            => '',
                'request_status'        => '',
                'request_code'          => '',
                'request_auth'          => '',
                'status_transaction'    => '',
                'payment'               => "Cybersource",
                'identifier_payment'    => "CYBERSOURCE",
                'value_payment'         => $solo4,
                'type_card'            => $typeCard
            ]
        );
        foreach ($product as $i => $pro)
        {

            $transactionsProduct = DetailTransactions::create(
                [
                    "transaction_uuid"  => $transactions->uuid,
                    "description"       => $pro['name'],
                    "quantity"          => $pro['quantity'],
                    "amount"            => $pro['amount'],
                    "subtotal"          => $pro['subtotal'],
                ]
            );
        }
        return $transactions;
    }
}
