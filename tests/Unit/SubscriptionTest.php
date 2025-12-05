<?php
namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;
use Tapsilat\APIException;
use Tapsilat\Models\SubscriptionCreateRequest;
use Tapsilat\Models\SubscriptionGetRequest;
use Tapsilat\Models\SubscriptionCancelRequest;
use Tapsilat\Models\SubscriptionRedirectRequest;
use Tapsilat\Models\SubscriptionCreateResponse;
use Tapsilat\Models\SubscriptionDetail;
use Tapsilat\Models\SubscriptionRedirectResponse;

class SubscriptionTest extends TestCase
{
    public function testCreateSubscriptionSuccess()
    {
        $expectedResponse = [
            'code' => 200,
            'message' => 'SUBSCRIPTION_CREATED',
            'reference_id' => 'sub-ref-123',
            'order_reference_id' => 'order-ref-456',
        ];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription/create', null, $this->anything())
            ->willReturn($expectedResponse);

        $subscription = new SubscriptionCreateRequest();
        $result = $apiMock->createSubscription($subscription);

        $this->assertInstanceOf(SubscriptionCreateResponse::class, $result);
    }

    public function testCreateSubscriptionFailure()
    {
        $apiErrorContent = ['code' => 400, 'error' => 'SUBSCRIPTION_CREATION_FAILED'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription/create', null, $this->anything())
            ->willThrowException(new APIException(400, $apiErrorContent['code'], $apiErrorContent['error']));

        $this->expectException(APIException::class);
        $this->expectExceptionMessage('SUBSCRIPTION_CREATION_FAILED');

        $subscription = new SubscriptionCreateRequest();
        $apiMock->createSubscription($subscription);
    }

    public function testGetSubscriptionSuccess()
    {
        $expectedResponse = [
            'reference_id' => 'sub-ref-123',
            'amount' => '100.00',
            'currency' => 'TRY',
            'is_active' => true,
        ];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription', null, $this->anything())
            ->willReturn($expectedResponse);

        $request = new SubscriptionGetRequest();
        $result = $apiMock->getSubscription($request);

        $this->assertInstanceOf(SubscriptionDetail::class, $result);
    }

    public function testGetSubscriptionNotFound()
    {
        $apiErrorContent = ['code' => 404, 'error' => 'SUBSCRIPTION_NOT_FOUND'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription', null, $this->anything())
            ->willThrowException(new APIException(404, $apiErrorContent['code'], $apiErrorContent['error']));

        $this->expectException(APIException::class);
        $this->expectExceptionMessage('SUBSCRIPTION_NOT_FOUND');

        $request = new SubscriptionGetRequest();
        $apiMock->getSubscription($request);
    }

    public function testListSubscriptionsSuccess()
    {
        $expectedResponse = [
            'page' => 1,
            'per_page' => 10,
            'rows' => [[], []],
            'total' => 2,
            'total_page' => 1,
        ];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $expectedParams = ['page' => 1, 'per_page' => 10];
        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('GET', '/subscription/list', $expectedParams)
            ->willReturn($expectedResponse);

        $result = $apiMock->listSubscriptions(1, 10);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testCancelSubscriptionSuccess()
    {
        $expectedResponse = ['message' => 'SUBSCRIPTION_CANCELLED', 'code' => 200];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription/cancel', null, $this->anything())
            ->willReturn($expectedResponse);

        $request = new SubscriptionCancelRequest();
        $result = $apiMock->cancelSubscription($request);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testCancelSubscriptionNotFound()
    {
        $apiErrorContent = ['code' => 404, 'error' => 'SUBSCRIPTION_NOT_FOUND'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription/cancel', null, $this->anything())
            ->willThrowException(new APIException(404, $apiErrorContent['code'], $apiErrorContent['error']));

        $this->expectException(APIException::class);
        $this->expectExceptionMessage('SUBSCRIPTION_NOT_FOUND');

        $request = new SubscriptionCancelRequest();
        $apiMock->cancelSubscription($request);
    }

    public function testRedirectSubscriptionSuccess()
    {
        $expectedResponse = ['url' => 'https://checkout.example.com/subscription/123'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription/redirect', null, $this->anything())
            ->willReturn($expectedResponse);

        $request = new SubscriptionRedirectRequest();
        $result = $apiMock->redirectSubscription($request);

        $this->assertInstanceOf(SubscriptionRedirectResponse::class, $result);
    }

    public function testRedirectSubscriptionFailure()
    {
        $apiErrorContent = ['code' => 400, 'error' => 'SUBSCRIPTION_REDIRECT_FAILED'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $apiMock->expects($this->once())
            ->method('makeRequest')
            ->with('POST', '/subscription/redirect', null, $this->anything())
            ->willThrowException(new APIException(400, $apiErrorContent['code'], $apiErrorContent['error']));

        $this->expectException(APIException::class);
        $this->expectExceptionMessage('SUBSCRIPTION_REDIRECT_FAILED');

        $request = new SubscriptionRedirectRequest();
        $apiMock->redirectSubscription($request);
    }
}
