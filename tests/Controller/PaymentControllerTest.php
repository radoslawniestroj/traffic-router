<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentControllerTest extends WebTestCase
{
    public function testEqualStrategyReturnsGatewayName(): void
    {
        $client = static::createClient();

        $payload = [
            'amount' => 10.0,
            'currency' => 'EUR',
            'strategy' => 'equal'
        ];

        $client->request('POST', '/api/payments', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('selected', $data);
        self::assertNotEmpty($data['selected']);
        self::assertArrayHasKey('gateway_load', $data);
        self::assertIsInt($data['gateway_load']);
    }

    public function testWeightedStrategyRespectsWeights(): void
    {
        $client = static::createClient();

        $payload = [
            'amount' => 15.0,
            'currency' => 'EUR',
            'strategy' => 'weighted',
            'weights' => [
                ['name' => 'gateway1', 'weight' => 90],
                ['name' => 'gateway2', 'weight' => 5],
                ['name' => 'gateway3', 'weight' => 5],
            ]
        ];

        $client->request('POST', '/api/payments', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('selected', $data);
        self::assertContains($data['selected'], ['gateway1', 'gateway2', 'gateway3']);
        self::assertArrayHasKey('gateway_load', $data);
        self::assertIsInt($data['gateway_load']);
    }
}
