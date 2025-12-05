<?php
namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;

class TapsilatAPITest extends TestCase
{
    public function testVerifyWebhook()
    {
        $payload = 'data';
        $secret = 'key';
        // hmac-sha256 of "data" with key "key"
        $signature = 'sha256=5031fe3d989c6d1537a013fa6e739da23463fdaec3b70137d828e36ace221bd0';

        $this->assertTrue(TapsilatAPI::verifyWebhook($payload, $signature, $secret));
        $this->assertFalse(TapsilatAPI::verifyWebhook($payload, 'sha256=invalid', $secret));
    }

    public function testHealthCheck()
    {
        $expectedResponse = ['status' => 'healthy', 'timestamp' => '2023-10-27T10:00:00Z'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/health')
            ->willReturn($expectedResponse);

        $result = $apiMock->healthCheck();

        $this->assertEquals($expectedResponse, $result);
    }
}
