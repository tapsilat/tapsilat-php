<?php
namespace Tapsilat\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;

class SubscriptionIntegrationTest extends TestCase
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

    public function testListSubscriptions()
    {
        $result = $this->api->listSubscriptions(1, 10);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
    }
}
