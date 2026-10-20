<?php

namespace Tapsilat\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tapsilat\OrderStatus;
use Tapsilat\TapsilatAPI;
use Tapsilat\WebhookEvent;

class WebhookTest extends TestCase
{
    // An order_lite callback signed the way admin's monosign.SignWebhook signs it; the expected
    // signature was computed with openssl, independently of the SDK.
    public const PAYLOAD = '{"data":{"id":"5f0c6f1e-1111-4a5b-9c1d-2e3f4a5b6c7d",'
        . '"orderReferenceId":"0b6a4f9e-2222-4c3d-8e9f-a1b2c3d4e5f6","conversationId":"wc-1001","status":"3"},'
        . '"type":"order_lite","token":"9d8c7b6a-3333-4e5f-a6b7-c8d9e0f1a2b3"}';
    public const SECRET = 'whsec_test';
    public const TIMESTAMP = 1726480000;
    public const SIGNATURE = '3e003945e8122172a03ae1aa56fd099b56e6ac11a8472c03e33809c7c480aeb6';

    private function header($signature = self::SIGNATURE, $timestamp = self::TIMESTAMP)
    {
        return 't=' . $timestamp . ',v1=' . $signature;
    }

    public function testVerifiesTheTapsilatSignatureHeader()
    {
        $this->assertTrue(
            TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), self::SECRET, 300, self::TIMESTAMP + 10)
        );
        $this->assertTrue(TapsilatAPI::verifyWebhook(
            self::PAYLOAD,
            ' t=' . self::TIMESTAMP . ', v1=' . strtoupper(self::SIGNATURE) . ' ',
            self::SECRET,
            300,
            self::TIMESTAMP
        ));
    }

    public function testRejectsATamperedBodyOrTheWrongSecret()
    {
        $tampered = str_replace('"status":"3"', '"status":"13"', self::PAYLOAD);

        $this->assertFalse(TapsilatAPI::verifyWebhook($tampered, $this->header(), self::SECRET, 300, self::TIMESTAMP));
        $this->assertFalse(TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), 'other', 300, self::TIMESTAMP));
        $this->assertFalse(TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), '', 300, self::TIMESTAMP));
        $this->assertFalse(TapsilatAPI::verifyWebhook(self::PAYLOAD, '', self::SECRET, 300, self::TIMESTAMP));
    }

    public function testRejectsAReplayedOrMalformedHeader()
    {
        $this->assertFalse(
            TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), self::SECRET, 300, self::TIMESTAMP + 301)
        );
        $this->assertFalse(
            TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), self::SECRET, 300, self::TIMESTAMP - 301)
        );
        $this->assertTrue(
            TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), self::SECRET, 0, self::TIMESTAMP + 86400)
        );
        $this->assertFalse(
            TapsilatAPI::verifyWebhook(self::PAYLOAD, 'v1=' . self::SIGNATURE, self::SECRET, 0, self::TIMESTAMP)
        );
        $this->assertFalse(
            TapsilatAPI::verifyWebhook(self::PAYLOAD, 't=' . self::TIMESTAMP, self::SECRET, 0, self::TIMESTAMP)
        );
        $this->assertFalse(TapsilatAPI::verifyWebhook(
            self::PAYLOAD,
            $this->header(self::SIGNATURE, '1726480000.5'),
            self::SECRET,
            0,
            self::TIMESTAMP
        ));
    }

    public function testAcceptsAnyOfSeveralV1Signatures()
    {
        $header = 't=' . self::TIMESTAMP . ',v1=' . str_repeat('0', 64) . ',v1=' . self::SIGNATURE;

        $this->assertTrue(TapsilatAPI::verifyWebhook(self::PAYLOAD, $header, self::SECRET, 300, self::TIMESTAMP));
    }

    public function testUsesTheCurrentTimeByDefault()
    {
        $now = time();
        $signature = hash_hmac('sha256', $now . '.' . self::PAYLOAD, self::SECRET);

        $this->assertTrue(TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header($signature, $now), self::SECRET));
        $this->assertFalse(TapsilatAPI::verifyWebhook(self::PAYLOAD, $this->header(), self::SECRET));
    }

    public function testParsesAnOrderLiteCallback()
    {
        $event = WebhookEvent::fromPayload(self::PAYLOAD);

        $this->assertSame(WebhookEvent::TYPE_ORDER_LITE, $event->type);
        $this->assertSame('9d8c7b6a-3333-4e5f-a6b7-c8d9e0f1a2b3', $event->token);
        $this->assertSame('0b6a4f9e-2222-4c3d-8e9f-a1b2c3d4e5f6', $event->getReferenceId());
        $this->assertSame('wc-1001', $event->getConversationId());
        $this->assertSame(OrderStatus::PAID, $event->getStatusCode());
        $this->assertSame('Paid', $event->getStatus());
        $this->assertTrue($event->isPaid());
        $this->assertFalse($event->isFailed());
    }

    public function testParsesAnOrderCallback()
    {
        $event = new WebhookEvent([
            'type' => 'order',
            'token' => 't-1',
            'data' => ['id' => 'o-1', 'reference_id' => 'ref-1', 'conversation_id' => 'c-1', 'status' => 13],
        ]);

        $this->assertSame('ref-1', $event->getReferenceId());
        $this->assertSame('c-1', $event->getConversationId());
        $this->assertSame('Failure', $event->getStatus());
        $this->assertTrue($event->isFailed());
    }

    public function testParsesAnOrderPaymentDetailCallback()
    {
        $event = new WebhookEvent([
            'type' => 'order_payment_detail',
            'token' => 't-2',
            'data' => [
                'id' => 'd-1',
                'order_reference_id' => 'ref-2',
                'conversation_id' => 'c-2',
                'order' => ['reference_id' => 'ref-2', 'id' => 'o-2', 'status' => 'Paid'],
                'order_payment_status' => ['code' => 0],
            ],
        ]);

        $this->assertSame('ref-2', $event->getReferenceId());
        $this->assertSame('c-2', $event->getConversationId());
        $this->assertSame(OrderStatus::PAID, $event->getStatusCode());
    }

    public function testParsesCallbacksWithoutAStatus()
    {
        $extended = new WebhookEvent([
            'type' => 'extended_order',
            'data' => [
                'id' => 'o-3',
                'reference_id' => 'ref-3',
                'terms' => [['reference_id' => 'term-1', 'status' => 2]],
            ],
        ]);
        $this->assertSame('ref-3', $extended->getReferenceId());
        $this->assertNull($extended->getConversationId());
        $this->assertNull($extended->getStatusCode());
        $this->assertFalse($extended->isPaid());

        $facilitator = new WebhookEvent([
            'type' => 'payment_facilitator',
            'data' => ['id' => 'p-1', 'payment_id' => 'pay-1', 'reference_id' => 'ref-4'],
        ]);
        $this->assertSame('ref-4', $facilitator->getReferenceId());
    }

    public function testIgnoresAZeroUuid()
    {
        $event = new WebhookEvent([
            'type' => 'order_payment_detail',
            'data' => [
                'order_reference_id' => '00000000-0000-0000-0000-000000000000',
                'order' => ['reference_id' => 'ref-5'],
            ],
        ]);

        $this->assertSame('ref-5', $event->getReferenceId());
    }

    public function testRejectsAPayloadThatIsNotAJsonObject()
    {
        $this->expectException(InvalidArgumentException::class);
        WebhookEvent::fromPayload('"order"');
    }

    public function testOrderStatusNamesAndGroups()
    {
        $this->assertSame(OrderStatus::WAITING_FOR_PAYMENT, OrderStatus::code('waiting for payment'));
        $this->assertSame(OrderStatus::EXPIRED, OrderStatus::code('21'));
        $this->assertSame('Partially refunded', OrderStatus::name(15));
        $this->assertNull(OrderStatus::code('SUCCESS'));
        $this->assertNull(OrderStatus::code(99));
        $this->assertNull(OrderStatus::name(null));

        $this->assertTrue(OrderStatus::isPaid('Sub merchant payment approved'));
        $this->assertTrue(OrderStatus::isPaid(OrderStatus::COMPLETED));
        $this->assertFalse(OrderStatus::isPaid('Waiting for payment'));
        $this->assertTrue(OrderStatus::isFailed('Expired'));
        $this->assertFalse(OrderStatus::isFailed('Paid'));
        $this->assertTrue(OrderStatus::isRefunded('10'));
    }
}
