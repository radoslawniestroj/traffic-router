<?php

namespace App\Service;

use App\Dto\Payment;
use App\Contract\PaymentGatewayInterface;
use InvalidArgumentException;

final class PaymentRouter
{
    private PaymentGatewayInterface $defaultGateway;

    /** registry of named gateways (optional) */
    /** @var array<string, PaymentGatewayInterface> */
    private array $namedGateways = [];

    public function __construct(PaymentGatewayInterface $defaultGateway, array $namedGateways = [])
    {
        $this->defaultGateway = $defaultGateway;
        $this->namedGateways = $namedGateways;
    }

    /**
     * Route payment using provided TrafficSplit strategy.
     * Returns the selected gateway.
     */
    public function routePayment(TrafficSplitInterface $strategy, Payment $payment): PaymentGatewayInterface
    {
        return $strategy->handlePayment($payment);
    }

    /**
     * Helper to get named gateway by id (if present).
     */
    public function getGatewayByName(string $name): ?PaymentGatewayInterface
    {
        return $this->namedGateways[$name] ?? null;
    }
}
