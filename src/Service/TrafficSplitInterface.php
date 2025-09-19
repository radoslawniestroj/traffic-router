<?php

namespace App\Service;

use App\Dto\Payment;
use App\Contract\PaymentGatewayInterface;

interface TrafficSplitInterface
{
    public function handlePayment(Payment $payment): PaymentGatewayInterface;
}
