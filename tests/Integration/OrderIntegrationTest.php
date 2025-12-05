<?php
namespace Tapsilat\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;
use Tapsilat\Models\BuyerDTO;
use Tapsilat\Models\OrderCreateDTO;

class OrderIntegrationTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $apiKey = getenv('TAPSILAT_API_KEY');
        if (empty($apiKey)) {
            $this->markTestSkipped('TAPSILAT_API_KEY not set in .env file');
        }
        $this->api = new TapsilatAPI($apiKey);
    }

    public function testCreateAndGetOrder()
    {
        // Create order
        $buyer = new BuyerDTO('Integration', 'Test', null, null, null, 'integration@test.com');
        $order = new OrderCreateDTO(100, 'TRY', 'tr', $buyer);

        $response = $this->api->createOrder($order);

        $this->assertNotEmpty($response->getReferenceId());
        // Auto-fetched checkout URL
        $this->assertNotEmpty($response->getCheckoutUrl());

        // Get order
        $orderDetail = $this->api->getOrder($response->getReferenceId());
        $this->assertEquals($response->getReferenceId(), $orderDetail->getData()['reference_id']);
    }

    public function testGetOrderList()
    {
        $result = $this->api->getOrderList(1, 10);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function testGetOrders()
    {
        $result = $this->api->getOrders('1', '10');
        $this->assertArrayHasKey('rows', $result);
    }

    public function testGetOrderSubmerchants()
    {
        $result = $this->api->getOrderSubmerchants(1, 10);
        $this->assertIsArray($result);
    }

    public function testGetOrganizationSettings()
    {
        $result = $this->api->getOrganizationSettings();
        $this->assertIsArray($result);
    }
}
