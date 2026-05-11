<?php
namespace Tapsilat\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;

class SystemEndpointsTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        // Use a partial mock to intercept makeRequest
        $this->api = $this->getMockBuilder(TapsilatAPI::class)
            ->setConstructorArgs(['test_key'])
            ->onlyMethods(['makeRequest'])
            ->getMock();
    }

    public function testGetSystemBasketItemTypes()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/basket-item-types')
            ->willReturn($expected);

        $result = $this->api->getSystemBasketItemTypes();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemErrorCodes()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/error-codes')
            ->willReturn($expected);

        $result = $this->api->getSystemErrorCodes();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemPaymentTermStatuses()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/payment-term-statuses')
            ->willReturn($expected);

        $result = $this->api->getSystemPaymentTermStatuses();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemProductTypes()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/product-types')
            ->willReturn($expected);

        $result = $this->api->getSystemProductTypes();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemShortcutTypes()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/shortcut-types')
            ->willReturn($expected);

        $result = $this->api->getSystemShortcutTypes();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemTransactionPaymentTypes()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/transaction-payment-types')
            ->willReturn($expected);

        $result = $this->api->getSystemTransactionPaymentTypes();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemTransactionPurposes()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/transaction-purposes')
            ->willReturn($expected);

        $result = $this->api->getSystemTransactionPurposes();
        $this->assertEquals($expected, $result);
    }

    public function testGetSystemTransactionStatuses()
    {
        $expected = ['data' => []];
        $this->api->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/system/transaction-statuses')
            ->willReturn($expected);

        $result = $this->api->getSystemTransactionStatuses();
        $this->assertEquals($expected, $result);
    }
}
