<?php

namespace App\Api\Service;

use App\Api\PaymentGatewayInterface;
use App\Dto\Payment;

interface TrafficSplitInterface
{
    public function handlePayment(Payment $payment): PaymentGatewayInterface;
}
