<?php

namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\APIException;
use Tapsilat\TapsilatAPI;
use Tapsilat\Models\BuyerDTO;
use Tapsilat\Models\CardInstallmentsRequest;
use Tapsilat\Models\CardTokenizeRequest;
use Tapsilat\Models\CheckoutDesignDTO;
use Tapsilat\Models\InstallmentTableRequest;
use Tapsilat\Models\MarketplaceApprovalPolicyInput;
use Tapsilat\Models\MarketplaceContractFilter;
use Tapsilat\Models\MarketplacePayoutApproval;
use Tapsilat\Models\MarketplacePayoutFilter;
use Tapsilat\Models\MarketplaceProfileUpdate;
use Tapsilat\Models\OrderChargeRequest;
use Tapsilat\Models\OrderCreateRequest;
use Tapsilat\Models\OrderListFilter;
use Tapsilat\Models\OrderResponse;
use Tapsilat\Models\OrgUserTokenCreateReq;
use Tapsilat\Models\SubmerchantPaymentAction;
use Tapsilat\Models\SubmerchantPaymentItemUpdate;
use Tapsilat\Models\SubmerchantPayoutEventRequest;
use Tapsilat\Models\VposUpdateRequest;

class PanelParityTest extends TestCase
{
    /** @var RecordingTapsilatAPI */
    private $api;

    protected function setUp(): void
    {
        $this->api = new RecordingTapsilatAPI();
    }

    public function testRequestsCarryTheBearerTokenAndJsonBody()
    {
        $this->api->on('POST', '/api/v1/order/charge', 200, '{"isSuccess":true,"statusCode":"00"}');

        $result = $this->api->chargeOrder(new OrderChargeRequest('ref-1', true));

        $request = $this->api->last('POST', '/api/v1/order/charge');
        $this->assertSame('Bearer test-key', $request['headers']['Authorization']);
        $this->assertSame('application/json', $request['headers']['Content-Type']);
        $this->assertSame(['order_reference_id' => 'ref-1', 'retry' => true], $request['json']);
        $this->assertTrue($result['isSuccess']);
    }

    public function testPathsAreEscapedAndListFiltersEncoded()
    {
        $this->api->on('GET', '/api/v1/order/ref%2F1', 200, '{"reference_id":"ref/1"}');
        $this->api->on('GET', '/api/v1/order/list', 200, '{"rows":[]}');

        $this->api->getOrder('ref/1');
        $this->api->getOrderListWithFilter(new OrderListFilter([
            'page' => 2,
            'per_page' => 50,
            'start_date' => '2026-01-01',
            'conversation_id' => 'c+1 x',
            'status' => 3,
        ]));

        $this->assertNotNull($this->api->last('GET', '/api/v1/order/ref%2F1'));
        $this->assertSame(
            'page=2&per_page=50&start_date=2026-01-01&conversation_id=c%2B1%20x&status=3',
            $this->api->last('GET', '/api/v1/order/list')['query']
        );
    }

    public function testValidationErrorsExposeCodeFieldErrorsAndRawBody()
    {
        $body = '{"message":"INVALID_REQUEST_BODY","code":400000,"errors":{"reference_id":"required"}}';
        $this->api->on('POST', '/api/v1/order/charge', 400, $body);

        try {
            $this->api->chargeOrder(new OrderChargeRequest(''));
            $this->fail('expected an APIException');
        } catch (APIException $e) {
            $this->assertSame(400, $e->statusCode);
            $this->assertSame(400000, $e->code);
            $this->assertSame('INVALID_REQUEST_BODY', $e->error);
            $this->assertSame(['reference_id' => 'required'], $e->errors);
            $this->assertSame($body, $e->rawBody);
        }
    }

    public function testErrorStringWithNumericCodeIsParsed()
    {
        $this->api->on(
            'GET',
            '/api/v2/submerchant-payouts/missing',
            424,
            '{"error":"payout is not eligible","code":9}'
        );

        try {
            $this->api->getMarketplacePayout('missing');
            $this->fail('expected an APIException');
        } catch (APIException $e) {
            $this->assertSame(424, $e->statusCode);
            $this->assertSame(9, $e->code);
            $this->assertSame('payout is not eligible', $e->error);
        }
    }

    public function testAnHtmlPageIsReportedAsAnApiError()
    {
        $this->api->on(
            'GET',
            '/api/v1/order/ref-1/status',
            200,
            '<!doctype html><html></html>',
            'text/html; charset=utf-8'
        );

        try {
            $this->api->getOrderStatus('ref-1');
            $this->fail('expected an APIException');
        } catch (APIException $e) {
            $this->assertSame(200, $e->statusCode);
            $this->assertStringContainsString('unexpected non-JSON response', $e->error);
            $this->assertSame('<!doctype html><html></html>', $e->rawBody);
        }
    }

    public function testNullTransactionsBecomeAnEmptyList()
    {
        $this->api->on('GET', '/api/v1/order/ref-1/transactions', 200, '[{"id":"tx-1"}]');
        $this->api->on('GET', '/api/v1/order/ref-2/transactions', 200, 'null');

        $this->assertSame([['id' => 'tx-1']], $this->api->getOrderTransactions('ref-1'));
        $this->assertSame([], $this->api->getOrderTransactions('ref-2'));
    }

    public function testPaymentDetailsByConversationId()
    {
        $this->api->on('POST', '/api/v1/order/payment-details', 200, '{"rows":[]}');

        $this->api->getOrderPaymentDetailsByConversationId('conv-1');

        $this->assertSame(
            ['conversation_id' => 'conv-1'],
            $this->api->last('POST', '/api/v1/order/payment-details')['json']
        );
    }

    public function testCardInstallmentsAndInstallmentTable()
    {
        $this->api->on(
            'GET',
            '/api/v1/card/installments',
            200,
            '{"is_installment_available":true,"installments":[{"installment":3}]}'
        );
        $this->api->on('GET', '/api/v1/card/installment-table', 200, '{"groups":[{"bank":"Bank","rows":[]}]}');

        $options = $this->api->getCardInstallments(new CardInstallmentsRequest([
            'bin' => '54066200', 'amount' => 100.5, 'currency' => 'TRY', 'is_three_d' => true,
        ]));
        $table = $this->api->getInstallmentTable(new InstallmentTableRequest(['amount' => 100, 'currency' => 'TRY']));

        $this->assertTrue($options['is_installment_available']);
        $this->assertSame('Bank', $table['groups'][0]['bank']);
        $this->assertSame(
            'bin=54066200&amount=100.5&currency=TRY&is_three_d=true',
            $this->api->last('GET', '/api/v1/card/installments')['query']
        );
        $this->assertSame(
            'amount=100&currency=TRY',
            $this->api->last('GET', '/api/v1/card/installment-table')['query']
        );
    }

    public function testSubmerchantSettlementRoutes()
    {
        $this->api->on('POST', '/api/v1/submerchants/payment/approve', 200, '{"operation_id":"op-1"}');
        $this->api->on('PUT', '/api/v1/submerchants/payment/item', 200, '{"operation_id":"op-2"}');
        $this->api->on('POST', '/api/v1/submerchant-payout-events', 202, '{"id":"ev-1"}');

        $action = new SubmerchantPaymentAction([
            'locale' => 'tr', 'conversation_id' => 'c-1', 'payment_transaction_id' => 'tx-1', 'acquirer' => 'acq',
        ]);
        $this->api->approveSubmerchantPayment($action);
        $this->api->updateSubmerchantPaymentItem(new SubmerchantPaymentItemUpdate([
            'locale' => 'tr', 'conversation_id' => 'c-1', 'payment_transaction_id' => 'tx-1',
            'sub_merchant_key' => 'key-1', 'sub_merchant_price' => '10.50', 'acquirer' => 'acq',
        ]));
        $event = $this->api->recordSubmerchantPayoutEvent(new SubmerchantPayoutEventRequest([
            'idempotency_key' => 'k-1',
            'order_reference' => 'ref-1',
            'item_id' => 'item-1',
            'event_type' => 'DELIVERED',
        ]));

        $this->assertSame($action->toArray(), $this->api->last('POST', '/api/v1/submerchants/payment/approve')['json']);
        $this->assertSame(
            '10.50',
            $this->api->last('PUT', '/api/v1/submerchants/payment/item')['json']['sub_merchant_price']
        );
        $this->assertSame('ev-1', $event['id']);
    }

    public function testVposUpdateSendsTheFullConfiguration()
    {
        $this->api->on('PATCH', '/api/v1/vpos/vpos-1', 200, '{"code":0,"message":"updated"}');
        $this->api->on('GET', '/api/v1/vpos', 200, '{"rows":[{"id":"vpos-1"}]}');

        $this->api->updateVpos('vpos-1', new VposUpdateRequest([
            'name' => 'Main', 'type' => 'BANK', 'currencies' => ['TRY'], 'failover' => true, 'pf_id' => 'pf-1',
            'tls' => false, 'root_ca' => 'ca', 'main_installment' => true,
        ]));
        $this->api->listVpos(1, 10, 'sub-1');

        $body = $this->api->last('PATCH', '/api/v1/vpos/vpos-1')['json'];
        $this->assertSame('BANK', $body['type']);
        $this->assertSame(['TRY'], $body['currencies']);
        $this->assertTrue($body['failover']);
        $this->assertFalse($body['tls']);
        $this->assertTrue($body['main_installment']);
        $this->assertSame(
            'page=1&per_page=10&suborganization_id=sub-1',
            $this->api->last('GET', '/api/v1/vpos')['query']
        );
    }

    public function testSavedCardRoutes()
    {
        $this->api->on('POST', '/api/v1/tokenization/card/tokenize', 200, '{"card_id":"card-1"}');
        $this->api->on('GET', '/api/v1/tokenization/card/list', 200, '{"rows":[]}');
        $this->api->on('DELETE', '/api/v1/tokenization/card/card%201', 200, '{"success":true}');

        $this->api->tokenizeCard(new CardTokenizeRequest([
            'card_number' => '4111111111111111', 'holder_name' => 'John Doe', 'expiry_month' => '12',
            'expiry_year' => '30', 'cvv' => '123',
        ]));
        $this->api->listSavedCards(2, 5);
        $deleted = $this->api->deleteSavedCard('card 1');

        $this->assertSame('page=2&per_page=5', $this->api->last('GET', '/api/v1/tokenization/card/list')['query']);
        $this->assertTrue($deleted['success']);
    }

    public function testMarketplaceRoutesUseApiV2AndKeepRevisionsOpaque()
    {
        $revision = '2026-10-19T10:00:00.123456789Z';
        $this->api->on('GET', '/api/v2/submerchants/s-1', 200, '{"id":"s-1","revision":"' . $revision . '"}');
        $this->api->on('PATCH', '/api/v2/submerchants/s-1', 200, '{"approval_required":true}');
        $this->api->on('GET', '/api/v2/marketplace/seller-contracts', 200, '{"contracts":{}}');
        $this->api->on('POST', '/api/v2/submerchant-payouts/approve', 202, '{"status":"accepted"}');
        $this->api->on('GET', '/api/v2/submerchant-payouts', 200, '{"rows":[]}');
        $this->api->on('DELETE', '/api/v2/marketplace/release-policies/rel-1', 204, '');

        $seller = $this->api->getMarketplaceSubmerchant('s-1');
        $this->api->updateMarketplaceSubmerchant('s-1', new MarketplaceProfileUpdate([
            'name' => 'Seller', 'revision' => $seller['revision'],
        ]));
        $this->api->getMarketplaceSellerContracts(new MarketplaceContractFilter([
            'currency_id' => 'TRY', 'seller_type' => 'PERSONAL', 'vpos_ids' => ['v-1', 'v-2'],
        ]));
        $accepted = $this->api->approveMarketplacePayouts(new MarketplacePayoutApproval(['ids' => ['p-1', 'p-2']]));
        $this->api->listMarketplacePayouts(
            new MarketplacePayoutFilter(['currency' => 'TRY', 'page' => 1, 'per_page' => 20])
        );
        $deleted = $this->api->deleteMarketplaceReleasePolicy('rel-1');

        $this->assertSame($revision, $this->api->last('PATCH', '/api/v2/submerchants/s-1')['json']['revision']);
        $this->assertSame(
            'currency_id=TRY&seller_type=PERSONAL&operation=create&vpos_id=v-1&vpos_id=v-2',
            $this->api->last('GET', '/api/v2/marketplace/seller-contracts')['query']
        );
        $this->assertSame(
            ['ids' => ['p-1', 'p-2']],
            $this->api->last('POST', '/api/v2/submerchant-payouts/approve')['json']
        );
        $this->assertSame('accepted', $accepted['status']);
        $this->assertSame(
            'currency=TRY&page=1&per_page=20',
            $this->api->last('GET', '/api/v2/submerchant-payouts')['query']
        );
        $this->assertSame([], $deleted);
    }

    public function testApprovalPolicyCurrencyUnitsAreResolvedToUuids()
    {
        $uuid = '3fa85f64-5717-4562-b3fc-2c963f66afa6';
        $this->api->on(
            'GET',
            '/api/v1/organization/currencies',
            200,
            '{"currencies":[{"id":"' . $uuid . '","currency_unit":"TRY"}]}'
        );
        $this->api->on('POST', '/api/v2/marketplace/approval-policies', 201, '{"id":"pol-1"}');
        $this->api->on('PATCH', '/api/v2/marketplace/approval-policies/pol-1', 200, '{}');

        $input = new MarketplaceApprovalPolicyInput([
            'name' => 'Large',
            'priority' => 1,
            'enabled' => true,
            'min_amount' => '1000',
            'currency_id' => 'try',
            'mode' => 'manual',
        ]);
        $this->api->createMarketplaceApprovalPolicy($input);
        $this->api->updateMarketplaceApprovalPolicy('pol-1', $input);

        $this->assertSame(
            $uuid,
            $this->api->last('POST', '/api/v2/marketplace/approval-policies')['json']['currency_id']
        );
        $this->assertSame(
            $uuid,
            $this->api->last('PATCH', '/api/v2/marketplace/approval-policies/pol-1')['json']['currency_id']
        );
        $lookups = array_filter($this->api->requests, function ($r) {
            return $r['path'] === '/api/v1/organization/currencies';
        });
        $this->assertCount(1, $lookups);
        $this->assertSame($uuid, $this->api->resolveCurrencyId(strtoupper($uuid)));
        $this->expectException(APIException::class);
        $this->api->resolveCurrencyId('XYZ');
    }

    public function testOrganizationLimitsByIdEscapesTheIdAndSendsTheQuery()
    {
        $this->api->on('GET', '/api/v1/organization/org%2F1/limits', 200, '{"limits":[]}');

        $this->api->getOrganizationLimitsById('org/1', ['currency' => 'TRY']);

        $this->assertSame('currency=TRY', $this->api->last('GET', '/api/v1/organization/org%2F1/limits')['query']);
    }

    public function testCompatibilityAliasesBuildTheirRequests()
    {
        $this->api->on('PATCH', '/api/v1/order/releated', 200, '{"is_success":true}');
        $this->api->on('POST', '/api/v1/order/terminate', 200, '{"code":0}');
        $this->api->on('POST', '/api/v1/order/callback', 200, '{"code":0}');

        $this->api->relatedUpdate('ref-1', ['related_reference_id' => 'ref-0']);
        $this->api->terminateOrder('ref-1');
        $this->api->manualCallback('ref-1', 'conv-1');

        $this->assertSame(
            ['reference_id' => 'ref-1', 'related_reference_id' => 'ref-0'],
            $this->api->last('PATCH', '/api/v1/order/releated')['json']
        );
        $this->assertSame(['reference_id' => 'ref-1'], $this->api->last('POST', '/api/v1/order/terminate')['json']);
        $this->assertSame(
            ['reference_id' => 'ref-1', 'conversation_id' => 'conv-1'],
            $this->api->last('POST', '/api/v1/order/callback')['json']
        );
    }

    public function testCreateOrderAcceptsAnInstallmentListAndBrandingFields()
    {
        $this->api->on('POST', '/api/v1/order/create', 200, '{"order_id":"order-1","reference_id":"ref-1"}');

        $design = new CheckoutDesignDTO();
        $design->accent_color = '#123456';
        $design->dark_mode_enabled = true;
        $design->dark_colors = ['text_color' => '#ffffff'];
        $order = new OrderCreateRequest(
            100,
            'TRY',
            'tr',
            new BuyerDTO('John', 'Doe'),
            null,
            null,
            $design,
            null,
            'conv-1',
            [3, 6]
        );
        $order->order_vpos_id = 'vpos-1';

        $response = $this->api->createOrder($order);

        $body = $this->api->last('POST', '/api/v1/order/create')['json'];
        $this->assertSame([3, 6], $body['enabled_installments']);
        $this->assertSame('#123456', $body['checkout_design']['accent_color']);
        $this->assertSame(['text_color' => '#ffffff'], $body['checkout_design']['dark_colors']);
        $this->assertSame('vpos-1', $body['order_vpos_id']);
        $this->assertSame('ref-1', $response->getReferenceId());
    }

    public function testUserTokenRequestCarriesTheUserId()
    {
        $this->api->on('POST', '/api/v1/organization/user/token', 200, '{"token":"tok"}');

        $this->api->createOrganizationUserToken(
            new OrgUserTokenCreateReq(null, 60, null, null, null, null, null, 'u-1')
        );

        $this->assertSame(
            ['expire' => 60, 'user_id' => 'u-1'],
            $this->api->last('POST', '/api/v1/organization/user/token')['json']
        );
    }

    public function testWebhookSignaturesAreAcceptedWithOrWithoutThePrefix()
    {
        $payload = '{"order":"1"}';
        $signature = hash_hmac('sha256', $payload, 'secret');

        $this->assertTrue(TapsilatAPI::verifyWebhook($payload, 'sha256=' . $signature, 'secret'));
        $this->assertTrue(TapsilatAPI::verifyWebhook($payload, strtoupper($signature), 'secret'));
        $this->assertFalse(TapsilatAPI::verifyWebhook('{"order":"2"}', $signature, 'secret'));
    }

    public function testHealthCheckReturnsThePlainTextStatus()
    {
        $this->api->on('GET', '/api/v1/health', 200, 'OK', 'text/plain; charset=utf-8');

        $this->assertSame(['status' => 'OK'], $this->api->healthCheck());
    }

    public function testVersionedBaseUrl()
    {
        $this->assertSame('https://panel.example/api/v2', $this->api->versionedBaseUrl('v2'));
        $this->assertSame(
            'https://panel.example/api/v2',
            (new TapsilatAPI('k', 10, 'https://panel.example/'))->versionedBaseUrl('v2')
        );
    }

    public function testOrderResponseSerializesToJson()
    {
        $this->assertSame('{"order_id":"o-1"}', json_encode(new OrderResponse(['order_id' => 'o-1'])));
    }

    public function testModelsRejectUnknownFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        new CardInstallmentsRequest(['bni' => '54066200']);
    }
}
