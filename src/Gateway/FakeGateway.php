<?php

namespace App\Gateway;

use App\Contract\PaymentGatewayInterface;
use App\Dto\Payment;

final class FakeGateway implements PaymentGatewayInterface
{
    private string $name;
    private int $handled = 0;

    public function __construct(string $name = 'fake')
    {
        $this->name = $name;
    }

    public function getTrafficLoad(): int
    {
        return $this->handled;
    }

    public function handlePayment(Payment $payment): void
    {
        // simulate processing by incrementing counter
        $this->handled++;
        // real gateways would call external API here
    }

    public function getName(): string
    {
        return $this->name;
    }
}
