<?php
namespace App\Helpers\payment;

use App\Models\sales\DetailTransactions;
use App\Models\sales\Transactions;

class Cybersource{

    static private $user;
    static private $password;
    static private $servicio;
    static private $estado_produccion;

    public static function initializer($payment,$production = false) {


//        $llaves = $payment;
        if ($production == false)
        {
            if (env('APP_ENV')=='local') {

                $llaves = $payment['test'];
                $estado = "test";
            }
            else{
                $llaves = $payment['live'];
                $estado = "live";
            }
        }
        else{
            if ($production == 'test')
            {
                $llaves = $payment['test'];
                $estado = "test";
            }
            else{
                $llaves = $payment['live'];
                $estado = "live";
            }
        }

        self::$user              = $llaves['user'];
        self::$password          = $llaves['apikey'];
        self::$servicio          = $llaves['url'];
        self::$estado_produccion = $estado;
    }

    public static function processPayment($payment,$production = false) {

//        return $payment;
        self::initializer($payment->credentials,$production);

        $cliente    = $payment->client;
        $tarjeta    = $payment->card_payment;
        $requestT   = $payment->request;
        $rules      = $payment->rules;
        $ip         = $payment->ip;
        $elTotal    = $payment->totalAmount;
        $currency   = $payment->currency;
        $transaction_uuid   = $payment->uuid_transaction;
        $info = (object)[
            'estado_produccion' => self::$estado_produccion
        ];


//        return extension_loaded('soap');
        try{
            $randme = mt_rand(10000, 99999);

            $headerns='http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

            $usernameNode      = new \SoapVar(self::$user, XSD_STRING, null, null, 'Username',$headerns);
            $passwordNode      = new \SoapVar(self::$password, XSD_STRING, null, null, 'Password',$headerns);
            $UsernameTokenNode = new \SoapVar([$usernameNode, $passwordNode], SOAP_ENC_OBJECT, null, null, 'UsernameToken',$headerns);
            $securityNode      = new \SoapVar([$UsernameTokenNode], SOAP_ENC_OBJECT, null, null, 'Security',$headerns);

            $header = new \SOAPHeader($headerns, 'Security',$securityNode);
            $client = new \SoapClient(self::$servicio, array());
            $client->__setSoapHeaders($header);

            $request2 = new \stdClass();

            $request2->merchantID = self::$user;
            $request2->merchantReferenceCode = $requestT . '-' . $randme;
            $request2->clientLibrary = "PHP";
            $request2->clientLibraryVersion = phpversion();
            $request2->clientEnvironment = php_uname();

            $ccAuthService = new \stdClass();
            $ccAuthService->run = "true";
            $request2->ccAuthService = $ccAuthService;

            $ccCaptureService = new \stdClass();
            $ccCaptureService->run = "true";
            $request2->ccCaptureService = $ccCaptureService;
            if (!empty($payment->deviceFinger)){
                $request2->deviceFingerprintID = $payment->deviceFinger;
            }
            $cityC = $rules['client']['city'];
            $postal_codeC = $rules['client']['postal_code'];
            $locationC = $rules['client']['location'];

            if (!empty($cliente->city)){$city = $cliente->city;}else{$city = $cityC;}
            if (!empty($cliente->location)){$location = $cliente->location;}else{$location = $locationC;}
            if (!empty($cliente->postal_code)){$postal_code = $cliente->postal_code;}else{$postal_code = $postal_codeC;}

            $billTo = new \stdClass();
            $billTo->firstName  = $cliente->first_name;
            $billTo->lastName   = !empty($cliente->last_name) ? $cliente->last_name : 'Cliente';
            $billTo->street1    = $location;
            $billTo->city       = $city;
            $billTo->state      = 'GT';
            $billTo->postalCode = !empty($postal_code) ? $postal_code : '01010';
            $billTo->country    = 'GT';
            $billTo->email      = $cliente->email;
            $billTo->ipAddress  = $ip;
            $request2->billTo   = $billTo;

            $card = new \stdClass();
            $card->accountNumber   = $tarjeta['number_card'];
            $card->expirationMonth = $tarjeta['expiration_month'];
            $card->expirationYear  = $tarjeta['expiration_year'];
            $card->cvNumber        = $tarjeta['cvv_card'];
            $request2->card        = $card;

            $purchaseTotals = new \stdClass();
            $purchaseTotals->currency = $currency;
            $request2->purchaseTotals = $purchaseTotals;

            $request2->item=[];
            $detailTransaction = DetailTransactions::where('transaction_uuid',$transaction_uuid)->get();
            if ($detailTransaction->count() > 0)
            {
                foreach ($detailTransaction as $i => $pro   ) {
                    $item0 = new \stdClass();
                    $item0->productSKU  = $pro->id;
                    $item0->productName = $pro->description;
                    $item0->unitPrice   = $pro->amount;
                    $item0->quantity    = $pro->quantity;
                    $item0->totalAmount = $pro->subtotal;
                    $item0->id          = $i+1;
                    $request2->item[]   = $item0;
                }
            }
            else
            {
                $item0 = new \stdClass();
                $item0->productSKU  = 'N/A';
                $item0->productName = 'Venta de productos';
                $item0->unitPrice   = $elTotal;
                $item0->quantity    = 1;
                $item0->totalAmount = $elTotal;
                $item0->id          = 1;
                $request2->item[]   = $item0;
            }

            $merchantDefinedData           = new \stdClass();
            $merchantDefinedData->field17  = $payment->perfil;
            $request2->merchantDefinedData = $merchantDefinedData;


            $reply = $client->runTransaction($request2);
            return (object)['result' => $reply, 'info' => $info,'code' => 200];


        }catch(\Exception $e){

            $reply = (object)[
                'decision'  => 'REJECT',
                'requestID' => '0000',
                'reasonCode'    => 234
            ];
            return (object)['result' => $reply, 'info' => $info,'message_error' => "Ocurrio un error, intente de nuevo ".$e->getMessage()." linea ".$e->getLine(),'code' => 400];
        }

    }

    public static function reverse_transactions($payment){

        self::initializer($payment->credentials);

        try{

            $headerns='http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

            $usernameNode      = new \SoapVar(self::$user, XSD_STRING, null, null, 'Username',$headerns);
            $passwordNode      = new \SoapVar(self::$password, XSD_STRING, null, null, 'Password',$headerns);
            $UsernameTokenNode = new \SoapVar([$usernameNode, $passwordNode], SOAP_ENC_OBJECT, null, null, 'UsernameToken',$headerns);
            $securityNode      = new \SoapVar([$UsernameTokenNode], SOAP_ENC_OBJECT, null, null, 'Security',$headerns);

            $header = new \SOAPHeader($headerns, 'Security',$securityNode);
            $client = new \SoapClient(self::$servicio, array());
            $client->__setSoapHeaders($header);

            $request_c = [

                'voidService' =>[
                    'voidRequestID' => $payment->requestID,
                    'run' => 'true',
                ],

                'merchantID' => self::$user,
                'merchantReferenceCode' => $payment->id_unico,

                'purchaseTotals' =>[
                    'currency' => $payment->currency,
                    'grandTotalAmount' => $payment->total,
                ],

            ];

            $reply = $client->runTransaction($request_c);

            return (object)['result' => $reply, 'code' => 200];

        }catch(\Exception $e){

            return (object)['errors' => "Ocurrio un error, intente de nuevo ".$e->getMessage()." linea ".$e->getLine(), 'code' => 400];

        }
    }

}
