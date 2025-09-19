<?php

namespace App\Service;

use App\Api\PaymentGatewayInterface;
use App\Api\Service\TrafficSplitInterface;
use App\Dto\Payment;

class PaymentRouter
{
    public function routePayment(TrafficSplitInterface $strategy, Payment $payment): PaymentGatewayInterface
    {
        return $strategy->handlePayment($payment);
    }
}
