<?php

namespace App\Controller;

use App\Dto\Payment;
use App\Gateway\FakeGateway;
use App\Service\EqualTrafficSplit;
use App\Service\WeightedTrafficSplit;
use App\Service\PaymentRouter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Minimal controller to demo routing.
 */
final class PaymentController extends AbstractController
{
    private PaymentRouter $router;
    /** @var FakeGateway[] */
    private array $demoGateways;

    public function __construct(PaymentRouter $router, array $demoGateways = [])
    {
        $this->router = $router;
        $this->demoGateways = $demoGateways;
    }

    #[Route('/api/payments', name: 'payments', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);

        $amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $currency = $data['currency'] ?? 'EUR';
        $strategy = $data['strategy'] ?? 'equal';

        $payment = new Payment($amount, $currency, uniqid('', true));

        // Build demo gateways if none were injected
        if (count($this->demoGateways) === 0) {
            $g1 = new FakeGateway('gateway1');
            $g2 = new FakeGateway('gateway2');
            $g3 = new FakeGateway('gateway3');
            $this->demoGateways = [$g1, $g2, $g3];
        }

        if ($strategy === 'equal') {
            $split = new EqualTrafficSplit($this->demoGateways);
            $gateway = $this->router->routePayment($split, $payment);
        } else {
            // prepare weights: prefer user-specified weights matching demo names
            $weightsInput = $data['weights'] ?? [];
            // Map weightsInput to gateways in order; if missing, fallback to equal weighting among remaining
            $weights = [];
            // if user provided explicit weights with gateway names, match them
            if (is_array($weightsInput) && count($weightsInput) > 0 && isset($weightsInput[0]['name'])) {
                // build by name
                $map = [];
                foreach ($this->demoGateways as $g) {
                    $map[$g->getName()] = $g;
                }
                $gwWithWeights = [];
                foreach ($weightsInput as $wi) {
                    $name = $wi['name'] ?? null;
                    $weight = isset($wi['weight']) ? (int)$wi['weight'] : 0;
                    if ($name !== null && isset($map[$name])) {
                        $gwWithWeights[] = ['gateway' => $map[$name], 'weight' => $weight];
                    }
                }
                // fallback: if user provided weights but didn't match names, build from ordered weights
                if (count($gwWithWeights) === 0) {
                    foreach ($this->demoGateways as $i => $g) {
                        $w = $weightsInput[$i]['weight'] ?? 0;
                        $gwWithWeights[] = ['gateway' => $g, 'weight' => (int)$w];
                    }
                }
            } else {
                // weights provided as ordered numbers?
                if (is_array($weightsInput) && count($weightsInput) > 0) {
                    $gwWithWeights = [];
                    foreach ($this->demoGateways as $i => $g) {
                        $w = $weightsInput[$i] ?? 0;
                        $gwWithWeights[] = ['gateway' => $g, 'weight' => (int)$w];
                    }
                } else {
                    // No weights provided — fallback to equal
                    $gwWithWeights = [];
                    $per = intdiv(100, count($this->demoGateways));
                    foreach ($this->demoGateways as $g) {
                        $gwWithWeights[] = ['gateway' => $g, 'weight' => $per];
                    }
                }
            }

            $split = new WeightedTrafficSplit($gwWithWeights);
            $gateway = $this->router->routePayment($split, $payment);
        }

        return $this->json([
            'selected' => $gateway->getName(),
            'gateway_load' => $gateway->getTrafficLoad(),
        ]);
    }
}
