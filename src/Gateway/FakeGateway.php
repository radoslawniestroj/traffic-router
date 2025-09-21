<?php

namespace App\Gateway;

use App\Api\PaymentGatewayInterface;
use App\Dto\Payment;

final class FakeGateway implements PaymentGatewayInterface
{
    private int $handled = 0;

    public function __construct(
        private readonly string $name = 'fake gateway'
    ) {
    }

    public function getTrafficLoad(): int
    {
        return $this->handled;
    }

    public function handlePayment(Payment $payment): void
    {
        $this->handled++;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
