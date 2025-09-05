<?php

use App\Enums\PaymentGateway;

return [
    PaymentGateway::CASH_ON_DELIVERY   => 'Pago contra entrega',
    PaymentGateway::E_WALLET => 'Billetera electrónica',
    PaymentGateway::PAYPAL => 'Paypal',

];
