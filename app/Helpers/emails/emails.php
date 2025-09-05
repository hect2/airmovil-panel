<?php
namespace App\Helpers\emails;

use App\Models\sales\DetailTransactions;
use App\Models\sales\Transactions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class emails
{
    public static function sendEmailPaymentAccept($client, $transactions)
    {
        $voucher = (object)[
            'payment'      => 'NeoNet',
            'correlativo'  => $transactions->number_tracking,
            'afiliacion'   => $transactions->merchant,
            'ubicacion'    => $client->location,
            'total'        => $transactions->total,
            'fecha'        => $transactions->datetz_transactions,
            'moneda'       => $transactions->currency,
            'tarjeta'      => $transactions->value_payment,
            'cliente'      => $client->name,
            'requestID'    => $transactions->request_id,
            'requestToken' => $transactions->request_auth,
            'visacuotas'   => false
        ];

        $customerPaper = false
            ? [0,0,430.00,410.00]
            : [0,0,350.00,420.00];


        $transaction = Transactions::where('uuid',$transactions->uuid)->first();



        $pdf = PDF::loadView('emails.pdf.voucherTransaccion', ['datosVoucher' => $voucher])->setPaper($customerPaper, "landscape");

        // Definir la ruta pública para guardar el PDF
        $fileName = emails::generateRandomStringCort().'_voucher' . '.pdf';
        $path = public_path('vouchers/' . $fileName);

        // Asegurarse de que la carpeta 'vouchers' exista
        if (!File::exists(public_path('vouchers'))) {
            File::makeDirectory(public_path('vouchers'), 0755, true);
        }

        // Guardar el PDF en la carpeta pública
        $pdf->save($path);

        // Generar y devolver la URL pública
        $url = url('vouchers/' . $fileName);
        if (!empty($url))
        {

            $transaction->fill([
                'url_voucher' => $url
            ]);
            $transaction->save();

            $detailTransaction = DetailTransactions::select('description','quantity','amount','subtotal')
                ->where('transaction_uuid',$transactions->uuid)
                ->get();

            $transaction->save();
            $emailClient = $client->email;
            $nameClient = $client->name;
            try {

//                Mail::send('emails.acceptPayment', ["emailClient"=>$emailClient,"nameClient"=>$nameClient,"name_business"=>'HOLANDESA',"product" => $detailTransaction,"transactions" => $transaction], function($message) use ($emailClient,$pdf) {
//                    $message->to($emailClient)
//                        ->subject('Detalle de pago')
//                        ->attachData($pdf->output(), "Comprobante.pdf");
//                });
            }  catch (\Exception $e) {
                $decision = $e->getMessage();
                return response()->json(['error' => 'true', 'code' => 400, 'message' =>$decision],400);
            }
        }

    }

    public static function sendReverseTransaction($transactions,$client)
    {
        $voucher = (object)[
            'payment'      => 'NeoNet',
            'correlativo'  => '',
            'afiliacion'   => '',
            'ubicacion'    => '',
            'total'        => floatval($transactions->total),
            'fecha'        => $transactions->date_transaction,
            'moneda'       => $transactions->currency,
            'tarjeta'      => $transactions->value_payment,
            'cliente'      => $transactions->client_name,
            'requestID'    => $transactions->request_id,
            'requestToken' => $transactions->request_auth,
            'number'       => '',
            'reference'    => $transactions->request_id,
            'visacuotas'   => false,
            'cardAID'      => '',
            'is_payfac'    => false,
        ];

        $customerPaper = false
            ? [0,0,430.00,410.00]
            : [0,0,350.00,420.00];

        $transaction = Transactions::where('uuid',$transactions->uuid)->first();
        $pdf = PDF::loadView('emails.pdf.voucherRverseTransaction', ['datosVoucher' => $voucher])->setPaper($customerPaper,"landscape");

        // Definir la ruta pública para guardar el PDF
        $fileName = emails::generateRandomStringCort().'_voucher' . '.pdf';
        $path = public_path('vouchers/' . $fileName);

        // Asegurarse de que la carpeta 'vouchers' exista
        if (!File::exists(public_path('vouchers'))) {
            File::makeDirectory(public_path('vouchers'), 0755, true);
        }

        // Guardar el PDF en la carpeta pública
        $pdf->save($path);

        // Generar y devolver la URL pública
        $url = url('vouchers/' . $fileName);
        if (!empty($url))
        {
            $transaction->fill([
                'url_voucher_reverse' => $url
            ]);
            $transaction->save();
            $emailClient = $client->email;
            $nameClient = $client->name;
            $detailTransaction = DetailTransactions::select('description','quantity','amount','subtotal')
                ->where('transaction_uuid',$transactions->uuid)
                ->get();
//            Mail::send('emails.reversePayment', ["emailClient"=>$emailClient,"nameClient"=>$nameClient,"name_business"=>'HOLANDESA',"product" => $detailTransaction,"transactions" => $transactions], function($message) use ($emailClient,$pdf){
//                $message->to($emailClient)
//                    ->subject('Detalle de anulación')
//                    ->attachData($pdf->output(), "Comprobante.pdf");
//            });
        }
    }

    public static function generateRandomStringCort($length =5){
        $nameImage = '';

        for ($i=0; $i < 4 ; $i++) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';

            for ($e = 0; $e < $length; $e++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            if($nameImage == ''){
                $nameImage .= $randomString.'-';
            } else {
                if ($i == 1) {
                    $nameImage .=$randomString;
                } else {
                    $nameImage .='-'.$randomString;
                }
            }
        }

        return $nameImage;

    }
}
