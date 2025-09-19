<?php

namespace App\Api;

use App\Dto\Payment;

interface PaymentGatewayInterface
{
    public function getTrafficLoad(): int;

    public function handlePayment(Payment $payment): void;

    public function getName(): string;
}
