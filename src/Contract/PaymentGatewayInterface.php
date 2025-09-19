<?php

namespace App\Contract;

use App\Dto\Payment;

interface PaymentGatewayInterface
{
    /**
     * Returns current traffic load (for observability/testing).
     * Implementations may return number of handled payments or similar metric.
     */
    public function getTrafficLoad(): int;

    /**
     * Handle given payment. Implementations should record/increment traffic.
     */
    public function handlePayment(Payment $payment): void;

    /**
     * Return gateway identifier (string) for logging/display.
     */
    public function getName(): string;
}
