<?php

namespace Database\Seeders;

use App\Models\sales\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use IlluminateAgnostic\Str\Support\Str;

class addCredentialPayment extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $credentialsCy = '{"live":{"url":"https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.110.wsdl","user":"visanetgt_bruselasbcsa","apikey":"hCuAtHIo7jykqaf/TpMIn3CztM3M1MNBywLNslmA36WLtpAF8ZWTf2tjgm6lbKipEeAYuxGD4q/tKW/PJTAo2wCq0xIgOk/sMNvjip+lOpBgWw43VXUN7b2S1fmjKtQnD55zbc69MrueZ5WJ4l0TucJArf6eebmXfwK8g+82Xd7JVev8HA0sqEcFBcEjGjH/EJePI19WgWvs88LWq1/m2j2Vm8enAQSEwnfMF+UYVIFzoAM15kaggQN+Cj9c5DYICjbAFDeKchGTOABSnyBQWbqdQE6HKevsFjfhlprt6nyQe+j5Z7X6yXbblW2Kvr3ldL9+rc3zcmSH76uJE5Y8tA==","status":"live"},"test":{"url":"https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.110.wsdl","user":"visanetgt_bruselasbcsa","apikey":"hCuAtHIo7jykqaf/TpMIn3CztM3M1MNBywLNslmA36WLtpAF8ZWTf2tjgm6lbKipEeAYuxGD4q/tKW/PJTAo2wCq0xIgOk/sMNvjip+lOpBgWw43VXUN7b2S1fmjKtQnD55zbc69MrueZ5WJ4l0TucJArf6eebmXfwK8g+82Xd7JVev8HA0sqEcFBcEjGjH/EJePI19WgWvs88LWq1/m2j2Vm8enAQSEwnfMF+UYVIFzoAM15kaggQN+Cj9c5DYICjbAFDeKchGTOABSnyBQWbqdQE6HKevsFjfhlprt6nyQe+j5Z7X6yXbblW2Kvr3ldL9+rc3zcmSH76uJE5Y8tA==","status":"test"}}';
        $functionCy = '{"rules": [{"parameters": [{"name": "ccCaptureService", "required": "true"}], "merchantDefinedData": [{"name": "field17", "value": "param:category_business", "required": "true"}]}], "client": {"city": "Guatemala", "location": "zona 10", "postal_code": "01010"}, "helper": "cybersource"}';

        $paymet = [
            ['payment_code' => 'CYBERSOURCE',   'type_payment' => 'CARDCREDIT', 'credentials'=> $credentialsCy,'function'=>$functionCy,'merchant'=>'CY','correlative'=>'1','currency'=>'GTQ','status'=>1]
        ];

        foreach ($paymet as $value) {
            $paymenMethod = PaymentMethod::where('payment_code',$value['payment_code'])->first();
            if (empty($paymenMethod))
            {
                PaymentMethod::create([
                    'uuid'          => Str::uuid(),
                    'payment_code'  => $value['payment_code'],
                    'type_payment'  => $value['type_payment'],
                    'credentials'   => $value['credentials'],
                    'function'      => $value['function'],
                    'merchant'      => $value['merchant'],
                    'correlative'   => $value['correlative'],
                    'currency'      => $value['currency'],
                    'status'        => $value['status'],
                ]);
            }

        }
    }
}
