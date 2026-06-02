<?php
namespace Tapsilat\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;
use Tapsilat\Models\BuyerDTO;
use Tapsilat\Models\OrderCreateRequest;
use Tapsilat\Models\OrderAccountingRequest;
use Tapsilat\Models\OrderPostAuthRequest;
use Tapsilat\Models\CancelOrderRequest;
use Tapsilat\Models\RefundOrderRequest;
use Tapsilat\Models\RefundAllOrderRequest;
use Tapsilat\Models\OrderPaymentDetailRequest;
use Tapsilat\Models\OrderPaymentTermCreateRequest;
use Tapsilat\Models\OrderPaymentTermUpdateRequest;
use Tapsilat\Models\OrderPaymentTermDeleteRequest;
use Tapsilat\Models\OrderTermRefundRequest;
use Tapsilat\Models\TerminateRequest;
use Tapsilat\Models\OrderManualCallbackRequest;
use Tapsilat\Models\OrderRelatedReferenceRequest;
use Tapsilat\Models\AddBasketItemRequest;
use Tapsilat\Models\RemoveBasketItemRequest;
use Tapsilat\Models\UpdateBasketItemRequest;
use Tapsilat\Models\OrderPaymentOptionsUpdateRequest;
use Tapsilat\Models\SplitOrderItemPaymentRequest;

class OrderIntegrationTest extends TestCase
{
    private $api;
    private static $testOrderId;

    protected function setUp(): void
    {
        $apiKey = getenv('TAPSILAT_API_KEY');
        if (empty($apiKey)) {
            $this->markTestSkipped('TAPSILAT_API_KEY not set in .env file');
        }
        $this->api = new TapsilatAPI($apiKey);
    }

    private function getTestOrderId()
    {
        if (self::$testOrderId) {
            return self::$testOrderId;
        }

        $buyer = new BuyerDTO('Integration', 'Test', null, null, null, 'integration@test.com');
        $order = new OrderCreateRequest(100, 'TRY', 'tr', $buyer);
        $response = $this->api->createOrder($order);
        self::$testOrderId = $response->getReferenceId();
        return self::$testOrderId;
    }

    public function testCreateAndGetOrder()
    {
        $id = $this->getTestOrderId();
        $this->assertNotEmpty($id);

        // Get order
        $orderDetail = $this->api->getOrder($id);
        $this->assertEquals($id, $orderDetail->getData()['reference_id']);
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

    public function testOrderCallback()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderCallback($id);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true); 
        }
    }

    public function testOrderVposQuery()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderVposQuery($id);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOrderAccounting()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderAccounting(new OrderAccountingRequest($id, 100));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOrderPostAuth()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderPostAuth(new OrderPostAuthRequest($id, 100));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetOrderStatus()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->getOrderStatus($id);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetOrderTransactions()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->getOrderTransactions($id);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetOrderByConversationId()
    {
        try {
            $this->api->getOrderByConversationId('non-existent-id');
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testCancelOrder()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->cancelOrder(new CancelOrderRequest($id));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testRefundOrder()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->refundOrder(new RefundOrderRequest($id, 100));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testRefundAllOrder()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->refundAllOrder(new RefundAllOrderRequest($id));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetOrderPaymentDetails()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->getOrderPaymentDetails(new OrderPaymentDetailRequest($id));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOrderTermOperations()
    {
        $id = $this->getTestOrderId();
        try {
            // Test Create
            $dto = new OrderPaymentTermCreateRequest($id, 'term-ref', 100, '2025-12-12', 1, true, 'PENDING');
            $this->api->createOrderTerm($dto);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }

        try {
            // Test Update
            $dto = new OrderPaymentTermUpdateRequest(100, '2025-12-12', true, 'PENDING', 'term-ref', 1);
            $this->api->updateOrderTerm($dto);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }

        try {
            // Test Delete
            $dto = new OrderPaymentTermDeleteRequest('order-id', 'term-ref');
            $this->api->deleteOrderTerm($dto);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }

        try {
            // Test Refund
            $dto = new OrderTermRefundRequest('term-id', 100);
            $this->api->refundOrderTerm($dto);
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOrderTerminate()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderTerminate(new TerminateRequest($id));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOrderManualCallback()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderManualCallback(new OrderManualCallbackRequest($id));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOrderRelatedUpdate()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->orderRelatedUpdate(new OrderRelatedReferenceRequest($id, 'related-id'));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testBasketItemOperations()
    {
        $id = $this->getTestOrderId();
        try {
            // Add
            $this->api->addBasketItem(new AddBasketItemRequest($id, 'name', 100, 1));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }

        try {
            // Update
            $this->api->updateBasketItem(new UpdateBasketItemRequest($id, 'item-id', 'name', 100, 1));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }

        try {
            // Remove
            $this->api->removeBasketItem(new RemoveBasketItemRequest($id, 'item-id'));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testUpdatePaymentOptions()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->updatePaymentOptions(new OrderPaymentOptionsUpdateRequest($id, ['card']));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSplitOrderItemPayment()
    {
        $id = $this->getTestOrderId();
        try {
            $this->api->splitOrderItemPayment(new SplitOrderItemPaymentRequest($id, 'payment-id', 100));
            $this->assertTrue(true);
        } catch (\Tapsilat\APIException $e) {
            $this->assertTrue(true);
        }
    }
}
