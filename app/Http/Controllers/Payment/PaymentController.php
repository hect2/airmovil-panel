<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\emails\emails;
use App\Helpers\payment\Cybersource;
use App\Helpers\processClient;
use App\Helpers\processTransactions;
use App\Helpers\validateCard;
use App\Http\Controllers\Controller;
use App\Models\sales\DetailTransactions;
use App\Models\sales\PaymentMethod;
use App\Models\sales\ResponseCode;
use App\Models\sales\SalesClients;
use App\Models\sales\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function paymentCybersource(Request $request)
    {
        try {

            $ip = $request->ip() ?? '192.168.1.1';
            $clientSales = new processClient();
            $client = $clientSales->createClient($request);

            $methodBusiness = PaymentMethod::where('payment_code','CYBERSOURCE')->where('currency',$request->currency)->where('status',1)->first();
            if (empty($methodBusiness))
            {
                return response()->json(['error' => 'true', 'code' => 400, 'message' =>"No cuentas con credenciales en: ".$request->currency],400);
            }

            $credentials = json_decode($methodBusiness->credentials,true);
            $function = json_decode($methodBusiness->function,true);

            $processTransactions = new processTransactions();
            $transactions = $processTransactions->crateTransactions($request,$client,$ip);

            $Datos = (object)[
                "rules"             => $function,
                "credentials"       => $credentials,
                "client"            => $client,
                "card_payment"      => $request->card_payment,
                "reference_code"    => $transactions->uuid.'-'.$transactions->id_order.'-'.strtotime("now"),
                "request"           => $transactions->uuid,
                "currency"          => $transactions->currency,
                "perfil"            => 'Abarroterías',
                "deviceFinger"      => isset($request['deviceFinger']) ? $request['deviceFinger'] : '',
                'ip'                => $ip,
                'totalAmount'       => $transactions->total,
                'uuid_transaction'  => $transactions->uuid
            ];

            $reply = Cybersource::processPayment($Datos);

            if($reply->result->decision == 'ACCEPT'){

                $transactions->fill(
                    [
                        'request_id'            => $reply->result->requestID,
                        'request_status'        => $reply->result->decision,
                        'request_code'          => $reply->result->reasonCode,
                        'request_auth'          => isset($reply->result->ccAuthReply->authorizationCode) ? $reply->result->ccAuthReply->authorizationCode: 0,
                        'status_transaction'    => $reply->result->decision,
                    ]
                );
                $transactions->save();

//                try {
                   emails::sendEmailPaymentAccept($client, $transactions);
//                    return response()->json(['error' => true, 'code' => 400, 'data' =>$pruebas],400);

//                } catch (\Exception $e) {
//
//                }

                $dateTransaction = $transactions->date_transaction;
                $date = Carbon::parse($dateTransaction)->format('d-m-Y');;
                $hour =  $dateTransaction->format('g:i A');
                $dataVoucher = [
                    'merchant'=>    $methodBusiness->merchant,
                    'request_id' => $transactions->request_id,
                    'code_payment' => $transactions->identifier_payment,
                    'date_transaction' => $date,
                    'hour_transactions' => $hour,
                    'last_card' => $transactions->value_payment,
                    'total' => $transactions->total,
                    'uuid_transaction' => $transactions->uuid,
                ];

                $transaction = Transactions::where('uuid',$transactions->uuid)->first();
                $objData = [
                    'url_voucher'=>$transaction->url_voucher,
                    'data_voucher' => $dataVoucher,
                    'decision' =>  $reply->result->decision,
                    'reasonCode' =>  $reply->result->reasonCode,
                    'requestID' =>  $reply->result->requestID,
                    'transactions' =>  $transactions->uuid
                ];
                return response()->json(['code'=>200,'error'=>false,'data'=>$objData],200);


            }
            else{

                $transactions->fill(
                    [
                        'request_id'            => $reply->result->requestID,
                        'request_status'        => $reply->result->decision,
                        'request_code'          => $reply->result->reasonCode,
                        'request_auth'          => isset($reply->result->ccAuthReply->authorizationCode) ? $reply->result->ccAuthReply->authorizationCode: 0,
                        'status_transaction'    => $reply->result->decision
                    ]
                );
                $transactions->save();



                $code = ResponseCode::where('code',$reply->result->reasonCode)->where('code_payment',$transactions->identifier_payment)
                    ->where('language','ES')
                    ->select(
                        'code',
                        'code_payment',
                        'language',
                        'description',
                        'message'
                    )
                    ->first();
                $data = [
                    'decision'          =>  $reply->result->decision,
                    'reasonCode'        =>  $reply->result->reasonCode,
                    'requestID'         =>  $reply->result->requestID,
                    'authorizationCode' =>  $reply->result->reasonCode,
                    'error_code'      => $code,
                ];

                return response()->json(['error' => true, 'code' => 400, 'data' =>$data],400);

            }
        }
        catch (\Exception $e) {
            $decision = $e->getMessage();
            return response()->json(['error' => 'true', 'code' => 400, 'message' =>$decision],400);
        }

    }

    public function reverseTransactions(Request $request)
    {
        try {
            $transactions =  Transactions::where('uuid',$request->transaction_uuid)->first();

            if (!$transactions || $transactions->identifier_payment !== 'CYBERSOURCE') {
                return response()->json(['error' => 'Transacción no válida o no compatible'], 404);
            }
            $client = SalesClients::where('uuid',$transactions->client_uuid)->first();
            $objData = emails::sendReverseTransaction($transactions,$client);
            return response()->json(['error' => 'false', 'code' => 200,'decision' => 'ACCEPT', 'data'=>$objData],200);

            $methodBusiness = PaymentMethod::where('payment_code','CYBERSOURCE')->where('currency',$transactions->currency)->first();
            if (!empty($methodBusiness))
            {
                $credentials = json_decode($methodBusiness->credentials,true);
                $data = (object)[
                    'currency'          => $transactions->currency,
                    'total'             => $transactions->total,
                    'id_empresa'        => $transactions->uuid,
                    'id_unico'          => 'Anulada-'.$transactions->currency.'-'.strtotime("now"),
                    'requestID'         => $transactions->request_id,
                    'estado_produccion' => $transactions->status_production, #test / live
                    "credentials"       => $credentials,
                ];
                $replyTarjeta = Cybersource::reverse_transactions($data);

                if($replyTarjeta->result->decision=='ACCEPT'){


                    $transactions->fill([
                        'status_transaction' => 'REVERSE',
                        'request_status' => 'REVERSE',
                        'reverse_request_id'=> $replyTarjeta->result->requestID,
                        'date_reverse'  => Carbon::now(),
                    ]);
                    $transactions->save();
                    $client = SalesClients::where('uuid',$transactions->client_uuid)->first();
                    emails::sendReverseTransaction($transactions,$client);
                    $transactions =  Transactions::where('uuid',$request->transaction_uuid)->first();
                    $objData = [
                        'reasonCode' => $replyTarjeta->result->reasonCode,
                        'requestID' => $transactions->request_id,
                        'requestIDReverse' => $replyTarjeta->result->requestID,
                        'url_voucher_reverse'=>$transactions->url_voucher_reverse
                    ];
                    return response()->json(['error' => 'false', 'code' => 200,'decision' => 'ACCEPT', 'data'=>$objData],200);

                }else{
                    $data = [
                        'decision' => 'REJECT',
                        'reasonCode' => $replyTarjeta,
                        'requestID' => $transactions->request_id
                    ];
                    return response()->json(['error' => 'true', 'code' => 400,'data'=>$data,'message' => 'Error al anular la transacción, código: '.$replyTarjeta->result->reasonCode],404);
                }
            }
            else{
                return response()->json(['error' => 'true', 'code' => 400, 'message' =>"No se tienes asignado la credenciales CYBERSOURCE"],400);

            }





        } catch (\Exception $e) {
            return response()->json(['error' => 'true', 'code' => 400, 'message' => $e->getMessage()],400);
        }
    }

}
