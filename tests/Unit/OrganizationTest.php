<?php
namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;
use Tapsilat\APIException;

class OrganizationTest extends TestCase
{
    public function testGetOrganizationSettingsSuccess()
    {
        $expectedResponse = [
            'ttl' => 3600,
            'retry_count' => 3,
            'allow_payment' => true,
            'session_ttl' => 1800,
            'custom_checkout' => false,
            'domain_address' => 'https://example.com',
            'checkout_domain' => 'https://checkout.example.com',
            'subscription_domain' => 'https://subscription.example.com',
        ];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/organization/settings')
            ->willReturn($expectedResponse);

        $result = $apiMock->getOrganizationSettings();

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('ttl', $result);
        $this->assertArrayHasKey('allow_payment', $result);
    }

    public function testGetOrganizationSettingsFailure()
    {
        $apiErrorContent = ['code' => 403, 'error' => 'ORGANIZATION_ACCESS_DENIED'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/organization/settings')
            ->willThrowException(new APIException(403, $apiErrorContent['code'], $apiErrorContent['error']));

        $this->expectException(APIException::class);
        $this->expectExceptionMessage('ORGANIZATION_ACCESS_DENIED');

        $apiMock->getOrganizationSettings();
    }
}
