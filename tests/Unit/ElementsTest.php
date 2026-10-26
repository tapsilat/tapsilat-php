<?php

namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\APIException;
use Tapsilat\Models\ElementsCardSummary;
use Tapsilat\Models\ElementsSession;
use Tapsilat\Models\ElementsSessionCreateRequest;
use Tapsilat\Models\ElementsSessionWithSecret;
use Tapsilat\TapsilatAPI;

class ElementsTest extends TestCase
{
    private const SECRET = 'es_test_8Kq2nV0rX4_secret_q8Zr0bM1';

    /** @var RecordingTapsilatAPI */
    private $api;

    protected function setUp(): void
    {
        $this->api = new RecordingTapsilatAPI();
    }

    private function stubCreate()
    {
        $this->api->on('POST', '/api/v1/elements/sessions', 200, json_encode([
            'id' => 'es_test_8Kq2nV0rX4',
            'purpose' => 'pay',
            'status' => 'requires_payment_method',
            'order_reference_id' => 'order-ref-1',
            'attempts' => 0,
            'max_attempts' => 3,
            'created_at' => '2026-10-22T09:00:00Z',
            'expires_at' => '2026-10-22T09:30:00Z',
            'client_secret' => self::SECRET,
            'elements_base_url' => 'https://checkout.tapsilat.dev',
        ]));
    }

    private function createPaySession()
    {
        return $this->api->createElementsSession(new ElementsSessionCreateRequest([
            'purpose' => ElementsSessionCreateRequest::PURPOSE_PAY,
            'order_reference_id' => 'order-ref-1',
            'save_card' => true,
        ]));
    }

    public function testCreatePaySession()
    {
        $this->stubCreate();

        $session = $this->createPaySession();

        $request = $this->api->last('POST', '/api/v1/elements/sessions');
        $this->assertNotNull($request);
        $this->assertSame('Bearer test-key', $request['headers']['Authorization']);
        $this->assertSame('application/json', $request['headers']['Content-Type']);
        $this->assertSame('{"purpose":"pay","order_reference_id":"order-ref-1","save_card":true}', $request['body']);

        $this->assertInstanceOf(ElementsSessionWithSecret::class, $session);
        $this->assertSame('es_test_8Kq2nV0rX4', $session->getId());
        $this->assertSame(ElementsSession::PURPOSE_PAY, $session->getPurpose());
        $this->assertSame(ElementsSession::STATUS_REQUIRES_PAYMENT_METHOD, $session->getStatus());
        $this->assertSame('order-ref-1', $session->getOrderReferenceId());
        $this->assertNull($session->getUserId());
        $this->assertSame(0, $session->getAttempts());
        $this->assertSame(3, $session->getMaxAttempts());
        $this->assertSame('2026-10-22T09:00:00Z', $session->getCreatedAt());
        $this->assertSame('2026-10-22T09:30:00Z', $session->getExpiresAt());
        $this->assertNull($session->getCompletedAt());
        $this->assertNull($session->getCard());
        $this->assertNull($session->getLastError());
        $this->assertSame(self::SECRET, $session->getClientSecret());
        $this->assertSame('https://checkout.tapsilat.dev', $session->getElementsBaseUrl());
    }

    public function testCreateTokenizeSession()
    {
        $this->api->on(
            'POST',
            '/api/v1/elements/sessions',
            200,
            '{"id":"es_test_1","purpose":"tokenize","status":"requires_payment_method","user_id":"user_123",'
            . '"client_secret":"es_test_1_secret_x"}'
        );

        $session = $this->api->createElementsSession(new ElementsSessionCreateRequest([
            'purpose' => ElementsSessionCreateRequest::PURPOSE_TOKENIZE,
            'user_id' => 'user_123',
            'user_email' => 'john@doe.com',
            'mode' => ElementsSessionCreateRequest::MODE_NON_SECURE,
        ]));

        $this->assertSame(
            ['purpose' => 'tokenize', 'user_id' => 'user_123', 'user_email' => 'john@doe.com', 'mode' => 3],
            $this->api->last('POST', '/api/v1/elements/sessions')['json']
        );
        $this->assertSame(ElementsSession::PURPOSE_TOKENIZE, $session->getPurpose());
        $this->assertSame('user_123', $session->getUserId());
        $this->assertSame('es_test_1_secret_x', $session->getClientSecret());
        $this->assertNull($session->getElementsBaseUrl());
    }

    public function testCreateRequestAlwaysSendsThePurposeAndOmitsUnsetFields()
    {
        $this->assertSame(['purpose' => ''], (new ElementsSessionCreateRequest())->toArray());
        $this->assertSame(
            ['purpose' => 'pay', 'order_reference_id' => 'order-ref-1', 'save_card' => false],
            (new ElementsSessionCreateRequest([
                'save_card' => false,
                'order_reference_id' => 'order-ref-1',
                'purpose' => 'pay',
            ]))->toArray()
        );
        $this->assertSame(1, ElementsSessionCreateRequest::MODE_SECURE);
        $this->assertSame(2, ElementsSessionCreateRequest::MODE_HALF_SECURE);
    }

    public function testGetSessionReadsTheCardAndLastError()
    {
        $this->api->on('GET', '/api/v1/elements/sessions/es_test_8Kq2nV0rX4', 200, json_encode([
            'id' => 'es_test_8Kq2nV0rX4',
            'purpose' => 'pay',
            'status' => 'failed',
            'order_reference_id' => 'order-ref-1',
            'attempts' => 3,
            'max_attempts' => 3,
            'completed_at' => '2026-10-22T09:12:00Z',
            'card' => ['id' => 'card-1', 'brand' => 'visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2030],
            'last_error' => ['code' => 'card_declined', 'message' => 'The card was declined.', 'retryable' => false],
        ]));

        $session = $this->api->getElementsSession('es_test_8Kq2nV0rX4');

        $request = $this->api->last('GET', '/api/v1/elements/sessions/es_test_8Kq2nV0rX4');
        $this->assertNotNull($request);
        $this->assertSame('Bearer test-key', $request['headers']['Authorization']);
        $this->assertNull($request['body']);
        $this->assertArrayNotHasKey('Content-Type', $request['headers']);

        $this->assertInstanceOf(ElementsSession::class, $session);
        $this->assertNotInstanceOf(ElementsSessionWithSecret::class, $session);
        $this->assertSame(ElementsSession::STATUS_FAILED, $session->getStatus());
        $this->assertSame(3, $session->getAttempts());
        $this->assertSame('2026-10-22T09:12:00Z', $session->getCompletedAt());
        $this->assertNull($session->getCreatedAt());

        $card = $session->getCard();
        $this->assertNotNull($card);
        $this->assertSame('card-1', $card->getId());
        $this->assertSame(ElementsCardSummary::BRAND_VISA, $card->getBrand());
        $this->assertSame('4242', $card->getLast4());
        $this->assertSame(12, $card->getExpMonth());
        $this->assertSame(2030, $card->getExpYear());

        $error = $session->getLastError();
        $this->assertNotNull($error);
        $this->assertSame('card_declined', $error->getCode());
        $this->assertSame('The card was declined.', $error->getMessage());
        $this->assertFalse($error->isRetryable());
    }

    public function testGetSessionReadsANullCardAndLastError()
    {
        $this->api->on(
            'GET',
            '/api/v1/elements/sessions/es_mock_1',
            200,
            '{"id":"es_mock_1","purpose":"pay","status":"requires_payment_method","card":null,"last_error":null}'
        );

        $session = $this->api->getElementsSession('es_mock_1');

        $this->assertSame('es_mock_1', $session->getId());
        $this->assertNull($session->getCard());
        $this->assertNull($session->getLastError());
    }

    public function testUnknownValuesAndFieldsAreKept()
    {
        $this->api->on(
            'GET',
            '/api/v1/elements/sessions/es_live_1',
            200,
            '{"id":"es_live_1","purpose":"subscribe","status":"on_hold","card":{"brand":"elo","last4":"0001"},'
            . '"last_error":{"code":"rate_limited","message":"slow down","retryable":true},"risk_score":12}'
        );

        $session = $this->api->getElementsSession('es_live_1');

        $this->assertSame('subscribe', $session->getPurpose());
        $this->assertSame('on_hold', $session->getStatus());
        $this->assertSame('elo', $session->getCard()->getBrand());
        $this->assertNull($session->getCard()->getExpMonth());
        $this->assertTrue($session->getLastError()->isRetryable());
        $this->assertSame(12, $session->toArray()['risk_score']);
    }

    public function testCancelSessionSendsNoBody()
    {
        $this->api->on(
            'POST',
            '/api/v1/elements/sessions/es_test_8Kq2nV0rX4/cancel',
            200,
            '{"id":"es_test_8Kq2nV0rX4","purpose":"pay","status":"canceled","attempts":0,"max_attempts":3}'
        );

        $session = $this->api->cancelElementsSession('es_test_8Kq2nV0rX4');

        $request = $this->api->last('POST', '/api/v1/elements/sessions/es_test_8Kq2nV0rX4/cancel');
        $this->assertNotNull($request);
        $this->assertSame('Bearer test-key', $request['headers']['Authorization']);
        $this->assertSame('', $request['body']);
        $this->assertArrayNotHasKey('Content-Type', $request['headers']);
        $this->assertSame(ElementsSession::STATUS_CANCELED, $session->getStatus());
    }

    public function testSessionIdIsEscapedAsOnePathSegment()
    {
        $this->api->on('GET', '/api/v1/elements/sessions/es_test_1%2F..%2Fx', 200, '{"id":"es_test_1"}');
        $this->api->on('POST', '/api/v1/elements/sessions/es_test_1%2F..%2Fx/cancel', 200, '{"id":"es_test_1"}');

        $this->api->getElementsSession('es_test_1/../x');
        $this->api->cancelElementsSession('es_test_1/../x');

        $this->assertNotNull($this->api->last('GET', '/api/v1/elements/sessions/es_test_1%2F..%2Fx'));
        $this->assertNotNull($this->api->last('POST', '/api/v1/elements/sessions/es_test_1%2F..%2Fx/cancel'));
    }

    public static function elementsErrors()
    {
        return [
            'create not enabled' => [
                'POST',
                '/api/v1/elements/sessions',
                403,
                '{"error":{"code":"elements_not_enabled","message":"Elements is not enabled for this organization"}}',
                function (TapsilatAPI $api) {
                    $api->createElementsSession(new ElementsSessionCreateRequest(['purpose' => 'pay']));
                },
                'elements_not_enabled',
                'Elements is not enabled for this organization',
                false,
            ],
            'get not found' => [
                'GET',
                '/api/v1/elements/sessions/es_test_missing',
                404,
                '{"error":{"code":"session_not_found","message":"session not found","retryable":false}}',
                function (TapsilatAPI $api) {
                    $api->getElementsSession('es_test_missing');
                },
                'session_not_found',
                'session not found',
                false,
            ],
            'cancel unavailable' => [
                'POST',
                '/api/v1/elements/sessions/es_test_1/cancel',
                503,
                '{"error":{"code":"unavailable","message":"try again","retryable":true}}',
                function (TapsilatAPI $api) {
                    $api->cancelElementsSession('es_test_1');
                },
                'unavailable',
                'try again',
                true,
            ],
        ];
    }

    /**
     * @dataProvider elementsErrors
     */
    public function testNestedErrorsExposeCodeMessageAndRetryable(
        $method,
        $path,
        $status,
        $body,
        callable $call,
        $code,
        $message,
        $retryable
    ) {
        $this->api->on($method, $path, $status, $body);

        try {
            $call($this->api);
            $this->fail('expected an APIException');
        } catch (APIException $e) {
            $this->assertSame($status, $e->statusCode);
            $this->assertSame($code, $e->code);
            $this->assertSame($message, $e->error);
            $this->assertSame($retryable, $e->retryable);
            $this->assertSame($body, $e->rawBody);
            $this->assertStringContainsString("code:{$code}\nerror:{$message}", $e->getMessage());
        }
    }

    public function testFlatErrorsKeepTheirShapeAndAreNotRetryable()
    {
        $this->api->on('GET', '/api/v1/order/missing', 400, '{"error":"order not found","code":5}');

        try {
            $this->api->getOrder('missing');
            $this->fail('expected an APIException');
        } catch (APIException $e) {
            $this->assertSame(5, $e->code);
            $this->assertSame('order not found', $e->error);
            $this->assertFalse($e->retryable);
        }
    }

    public function testPrintingASessionRedactsTheClientSecret()
    {
        $this->stubCreate();
        $session = $this->createPaySession();

        ob_start();
        var_dump($session);
        $dumped = ob_get_clean();
        $printed = print_r($session, true);

        foreach ([$dumped, $printed] as $output) {
            $this->assertStringNotContainsString(self::SECRET, $output);
            $this->assertStringContainsString(ElementsSession::REDACTED, $output);
            $this->assertStringContainsString('es_test_8Kq2nV0rX4', $output);
        }

        $this->assertSame(self::SECRET, $session->getClientSecret());
        $this->assertSame(self::SECRET, $session->toArray()['client_secret']);
        $this->assertStringContainsString('"client_secret":"' . self::SECRET . '"', json_encode($session));
    }

    public function testDebugOutputDoesNotPrintTheClientSecret()
    {
        $this->stubCreate();
        $this->api->setDebug(true);

        ob_start();
        $this->createPaySession();
        $output = ob_get_clean();

        $this->assertStringContainsString('Request: POST https://panel.example/api/v1/elements/sessions', $output);
        $this->assertStringNotContainsString(self::SECRET, $output);
        $this->assertStringNotContainsString('test-key', $output);
    }
}
