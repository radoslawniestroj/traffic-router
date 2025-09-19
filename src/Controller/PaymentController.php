<?php

namespace App\Controller;

use App\Api\PaymentGatewayInterface;
use App\Dto\Payment;
use App\Gateway\FakeGateway;
use App\Service\TrafficSplit\Random\EqualTrafficSplit as RandomEqualTrafficSplit;
use App\Service\TrafficSplit\Random\WeightedTrafficSplit as RandomWeightedTrafficSplit;
use App\Service\PaymentRouter;
use App\Service\TrafficSplit\RoundRobin\EqualTrafficSplit as RoundRobinEqualTrafficSplit;
use App\Service\TrafficSplit\RoundRobin\WeightedTrafficSplit as RoundRobinWeightedTrafficSplit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class PaymentController extends AbstractController
{
    private const RANDOM_EQUAL = 'random_equal';
    private const RANDOM_WEIGHT = 'random_weight';
    private const ROBIN_EQUAL = 'robin_equal';
    private const ROBIN_WEIGHT = 'robin_weight';

    public function __construct(
        private readonly PaymentRouter $router,
        private array $gateways = []
    ) {
    }

    #[Route('/payments', name: 'payments', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);

        $amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $currency = $data['currency'] ?? 'EUR';
        $strategy = $data['strategy'] ?? 'equal';

        $payment = new Payment($amount, $currency, uniqid('', true));

        if (count($this->gateways) === 0) {
            $this->gateways = [
                new FakeGateway('gateway1'),
                new FakeGateway('gateway2'),
                new FakeGateway('gateway3')
            ];
        }

        $gateway = match ($strategy) {
            self::RANDOM_EQUAL => $this->getEqualSplitGateway($payment, RandomEqualTrafficSplit::class),
            self::RANDOM_WEIGHT => $this->getWeightSplitGateway($payment, RandomWeightedTrafficSplit::class),
            self::ROBIN_EQUAL => $this->getEqualSplitGateway($payment, RoundRobinEqualTrafficSplit::class),
            self::ROBIN_WEIGHT => $this->getWeightSplitGateway($payment, RoundRobinWeightedTrafficSplit::class),
            default => $this->getEqualSplitGateway($payment, RandomEqualTrafficSplit::class),
        };

        return $this->json([
            'selected' => $gateway->getName(),
            'gateway_load' => $gateway->getTrafficLoad(),
        ]);
    }

    private function getEqualSplitGateway(Payment $payment, string $trafficSplit): PaymentGatewayInterface
    {
        return $this->router->routePayment(new $trafficSplit($this->gateways), $payment);
    }

    private function getWeightSplitGateway(Payment $payment, string $trafficSplit): PaymentGatewayInterface
    {
        $weightsInput = $data['weights'] ?? [];
        $gatewaysWithWeights = [];

        if (is_array($weightsInput) && count($weightsInput) > 0 && isset($weightsInput[0]['name'])) {
            $map = [];

            foreach ($this->gateways as $gateway) {
                $map[$gateway->getName()] = $gateway;
            }

            foreach ($weightsInput as $weight) {
                $name = $weight['name'] ?? null;
                $weight = isset($weight['weight']) ? (int)$weight['weight'] : 0;
                if ($name !== null && isset($map[$name])) {
                    $gatewaysWithWeights[] = ['gateway' => $map[$name], 'weight' => $weight];
                }
            }

            if (count($gatewaysWithWeights) === 0) {
                foreach ($this->gateways as $i => $gateway) {
                    $weight = $weightsInput[$i]['weight'] ?? 0;
                    $gatewaysWithWeights[] = ['gateway' => $gateway, 'weight' => (int)$weight];
                }
            }
        } else {
            if (is_array($weightsInput) && count($weightsInput) > 0) {
                foreach ($this->gateways as $i => $gateway) {
                    $weight = $weightsInput[$i] ?? 0;
                    $gatewaysWithWeights[] = ['gateway' => $gateway, 'weight' => (int)$weight];
                }
            } else {
                $weight = intdiv(100, count($this->gateways));
                foreach ($this->gateways as $gateway) {
                    $gatewaysWithWeights[] = ['gateway' => $gateway, 'weight' => $weight];
                }
            }
        }

        return $this->router->routePayment(new $trafficSplit($gatewaysWithWeights), $payment);
    }
}
