<?php

namespace App\Http\Controllers;

use App\Exports\TransactionExport;
use App\Helpers\emails\emails;
use App\Helpers\payment\Cybersource;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\TransactionSalesResource;
use App\Models\sales\PaymentMethod;
use App\Models\sales\SalesClients;
use App\Models\sales\Transactions;
use App\Services\TransactionSalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class TransactionSalesController extends AdminController
{
    public TransactionSalesService $transactionService;

    public function __construct(TransactionSalesService $transactionService)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
        $this->middleware(['permission:transactions'])->only('index', 'export');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return TransactionSalesResource::collection($this->transactionService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }



    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new TransactionExport($this->transactionService, $request), 'Transaction.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function reverse(Request $request)
    {

        try {
            $transactions =  Transactions::where('uuid',$request->uuid)->first();

            if (!$transactions || $transactions->identifier_payment !== 'CYBERSOURCE') {
                return response()->json(['error' => false, 'code' => 200,'message' => 'No es posible procesar la transaccion'],200);
            }


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
                    $transactions =  Transactions::where('uuid',$request->uuid)->first();
                    $objData = [
                        'reasonCode' => $replyTarjeta->result->reasonCode,
                        'requestID' => $transactions->request_id,
                        'requestIDReverse' => $replyTarjeta->result->requestID,
                        'url_voucher_reverse'=>$transactions->url_voucher_reverse
                    ];
                    return response()->json(['error' => false, 'code' => 200,'decision' => 'ACCEPT', 'data'=>$objData],200);

                }else{
                    $data = [
                        'decision' => 'REJECT',
                        'reasonCode' => $replyTarjeta,
                        'requestID' => $transactions->request_id
                    ];
                    return response()->json(['error' => true, 'code' => 400,'data'=>$data,'message' => 'Error al anular la transacción, código: '.$replyTarjeta->result->reasonCode],404);
                }
            }
            else{
                return response()->json(['error' => true, 'code' => 400, 'message' =>"No se tienes asignado la credenciales CYBERSOURCE"],400);

            }





        } catch (\Exception $e) {
            return response()->json(['error' => 'true', 'code' => 400, 'message' => $e->getMessage()],400);
        }
    }

    public function show(Request $request)
    {
        $transactions =  Transactions::with('detail','error','captures')->where('uuid',$request->uuid)->first();
        return response()->json(['error' => false, 'code' => 200, 'data' =>$transactions],200);

    }
}
