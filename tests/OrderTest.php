<?php
namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;
use Tapsilat\APIException;
use Tapsilat\Models\BuyerDTO;
use Tapsilat\Models\OrderCreateDTO;
use Tapsilat\Models\BasketItemDTO;
use Tapsilat\Models\BasketItemPayerDTO;
use Tapsilat\Models\BillingAddressDTO;
use Tapsilat\Models\CheckoutDesignDTO;
use Tapsilat\Models\MetadataDTO;
use Tapsilat\Models\OrderCardDTO;
use Tapsilat\Models\PaymentTermDTO;
use Tapsilat\Models\OrderPFSubMerchantDTO;
use Tapsilat\Models\OrderResponse;
use Tapsilat\Models\RefundOrderDTO;
use Tapsilat\Models\ShippingAddressDTO;
use Tapsilat\Models\SubmerchantDTO;
use Tapsilat\Models\SubOrganizationDTO;

class OrderTest extends TestCase
{
    public function testOrderToArray()
    {
        $buyer = new BuyerDTO("John", "Doe", null, null, null, "test@example.com");
        $order = new OrderCreateDTO(
            100,
            "TRY",
            "tr",
            $buyer
        );
        $jsonData = $order->toArray();
        $this->assertEquals(100, $jsonData["amount"]);
        $this->assertEquals("TRY", $jsonData["currency"]);
        $this->assertEquals("tr", $jsonData["locale"]);
        $this->assertEquals("John", $jsonData["buyer"]["name"]);
        $this->assertEquals("Doe", $jsonData["buyer"]["surname"]);
        $this->assertEquals("test@example.com", $jsonData["buyer"]["email"]);
    }

    public function testBasketItemPayerDTOToArray()
    {
        $payer = new BasketItemPayerDTO("uskudar", "123456789", null, null, "PERSONAL");
        $payerArray = $payer->toArray();
        $this->assertEquals("uskudar", $payerArray["address"]);
        $this->assertEquals("PERSONAL", $payerArray["type"]);
        $this->assertEquals("123456789", $payerArray["reference_id"]);
        $this->assertArrayNotHasKey("tax_office", $payerArray);
    }

    public function testBasketItemDTOToArray()
    {
        $payerData = new BasketItemPayerDTO("uskudar", null, null, null, "BUSINESS");
        $item = new BasketItemDTO(
            null, null, null, null, null, null,
            "BI101", "PHYSICAL", "Binocular", null, $payerData, 19.99, 1
        );
        $itemArray = $item->toArray();
        $this->assertEquals("BI101", $itemArray["id"]);
        $this->assertEquals("Binocular", $itemArray["name"]);
        $this->assertEquals(19.99, $itemArray["price"]);
        $this->assertEquals(1, $itemArray["quantity"]);
        $this->assertEquals("PHYSICAL", $itemArray["item_type"]);
        $this->assertEquals("uskudar", $itemArray["payer"]["address"]);
        $this->assertEquals("BUSINESS", $itemArray["payer"]["type"]);
        $this->assertArrayNotHasKey("category1", $itemArray);
    }

    public function testBillingAddressDTOToArray()
    {
        $billing = new BillingAddressDTO("uskudar", null, null, "Istanbul", "Jane Doe", null, "TR");
        $billingArray = $billing->toArray();
        $this->assertEquals("uskudar", $billingArray["address"]);
        $this->assertEquals("Istanbul", $billingArray["city"]);
        $this->assertEquals("TR", $billingArray["country"]);
        $this->assertEquals("Jane Doe", $billingArray["contact_name"]);
        $this->assertArrayNotHasKey("zip_code", $billingArray);
    }

    public function testCheckoutDesignDTOToArray()
    {
        $design = new CheckoutDesignDTO(null, null, null, null, "http://example.com/logo.png", null, "#FF0000");
        $designArray = $design->toArray();
        $this->assertEquals("#FF0000", $designArray["pay_button_color"]);
        $this->assertEquals("http://example.com/logo.png", $designArray["logo"]);
        $this->assertArrayNotHasKey("input_background_color", $designArray);
    }

    public function testMetadataDTOToArray()
    {
        $meta = new MetadataDTO("key", "value");
        $metaArray = $meta->toArray();
        $this->assertEquals("key", $metaArray["key"]);
        $this->assertEquals("value", $metaArray["value"]);
    }

    public function testOrderCardDTOToArray()
    {
        $card = new OrderCardDTO("123456789", 1);
        $cardArray = $card->toArray();
        $this->assertEquals("123456789", $cardArray["card_id"]);
        $this->assertEquals(1, $cardArray["card_sequence"]);
    }

    public function testPaymentTermDTOToArray()
    {
        $term = new PaymentTermDTO(50.0, null, "2025-10-21T23:59:59Z", null, null, "PENDING", null, 1);
        $termArray = $term->toArray();
        $this->assertEquals(50.0, $termArray["amount"]);
        $this->assertEquals("2025-10-21T23:59:59Z", $termArray["due_date"]);
        $this->assertEquals("PENDING", $termArray["status"]);
        $this->assertEquals(1, $termArray["term_sequence"]);
        $this->assertArrayNotHasKey("data", $termArray);
    }

    public function testOrderPFSubMerchantDTOToArray()
    {
        $pfSub = new OrderPFSubMerchantDTO(null, null, null, null, "123456789", "1234", "John Doe");
        $pfSubArray = $pfSub->toArray();
        $this->assertEquals("123456789", $pfSubArray["id"]);
        $this->assertEquals("John Doe", $pfSubArray["name"]);
        $this->assertEquals("1234", $pfSubArray["mcc"]);
        $this->assertArrayNotHasKey("address", $pfSubArray);
    }

    public function testShippingAddressDTOToArray()
    {
        $shipping = new ShippingAddressDTO("uskudar", "Istanbul", "Jane Doe", "Turkey");
        $shippingArray = $shipping->toArray();
        $this->assertEquals("uskudar", $shippingArray["address"]);
        $this->assertEquals("Istanbul", $shippingArray["city"]);
        $this->assertEquals("Turkey", $shippingArray["country"]);
        $this->assertEquals("Jane Doe", $shippingArray["contact_name"]);
    }

    public function testSubOrganizationDTOToArray()
    {
        $subOrg = new SubOrganizationDTO(
            null, null, null, null, null, null, null, null, null,
            "ACME Inc.", "ACME Inc.", null, "sub merchant key"
        );
        $subOrgArray = $subOrg->toArray();
        $this->assertEquals("ACME Inc.", $subOrgArray["organization_name"]);
        $this->assertEquals("sub merchant key", $subOrgArray["sub_merchant_key"]);
        $this->assertEquals("ACME Inc.", $subOrgArray["legal_company_title"]);
        $this->assertArrayNotHasKey("acquirer", $subOrgArray);
    }

    public function testSubmerchantDTOToArray()
    {
        $submerchant = new SubmerchantDTO(20.49, "merchant reference id", "BI101");
        $submerchantArray = $submerchant->toArray();
        $this->assertEquals(20.49, $submerchantArray["amount"]);
        $this->assertEquals("merchant reference id", $submerchantArray["merchant_reference_id"]);
        $this->assertEquals("BI101", $submerchantArray["order_basket_item_id"]);
    }

    public function testRefundOrderDTOToArray()
    {
        $dto = new RefundOrderDTO(50.0, "ref123", "item001");
        $dtoArray = $dto->toArray();
        $this->assertEquals(50.0, $dtoArray["amount"]);
        $this->assertEquals("ref123", $dtoArray["reference_id"]);
        $this->assertEquals("item001", $dtoArray["order_item_id"]);

        $dtoFull = new RefundOrderDTO(100.0, "ref456", "item002", "payment002");
        $dtoFullArray = $dtoFull->toArray();
        $this->assertEquals(100.0, $dtoFullArray["amount"]);
        $this->assertEquals("ref456", $dtoFullArray["reference_id"]);
        $this->assertEquals("item002", $dtoFullArray["order_item_id"]);
        $this->assertEquals("payment002", $dtoFullArray["order_item_payment_id"]);
    }

    // Mock API request tests will be added here
    // Since PHPUnit doesn't have the same mocking capabilities as Python's pytest
    // We'll need to implement mocking differently or use a library like Mockery

    public function testCreateOrderSuccess()
    {
        // This test would require mocking the HTTP client
        // For now, we'll create a basic structure
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCreateOrderWithBasketItems()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderFailure()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderByConversationIdSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderByConversationIdFailure()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderList()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderSubmerchants()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetCheckoutUrlSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCancelOrderNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCancelOrderSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testRefundOrderSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testRefundOrderFailure()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testRefundAllOrderSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testRefundAllOrderFailure()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderPaymentDetailsSuccessWithRefId()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderPaymentDetailsSuccessWithConvId()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderPaymentDetailsNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderStatusSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderStatusNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderTransactionsSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderTransactionsNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderTermSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testGetOrderTermFailure()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCreateOrderTermSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCreateOrderTermFailureExceedsOrderAmount()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCreateOrderTermFailureStatusInvalid()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testDeleteOrderTermSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testDeleteOrderTermFailure()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testUpdateOrderTermSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testUpdateOrderTermNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testOrderTerminateOrderNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testOrderTerminateOrderSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testOrderCallbackFailed()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testOrderCallbackOrderSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testOrderRelatedUpdateNotFound()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testOrderRelatedUpdateSuccess()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCreateOrderWithGsmValidation()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }

    public function testCreateOrderWithInvalidGsmRaisesException()
    {
        $this->markTestIncomplete('API mocking needs to be implemented');
    }
}
