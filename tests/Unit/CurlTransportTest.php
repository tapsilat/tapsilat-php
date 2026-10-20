<?php
namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\APIException;
use Tapsilat\TapsilatAPI;
use Tapsilat\Models\OrderPaymentTermDeleteRequest;
use Tapsilat\Models\SubmerchantPaymentItemUpdate;

/**
 * Sends real requests through curl to PHP's built-in web server, which echoes what it received.
 */
class CurlTransportTest extends TestCase
{
    private static $process;
    private static $dir;
    private static $port;

    public static function setUpBeforeClass(): void
    {
        self::$dir = sys_get_temp_dir() . '/tapsilat-php-echo-' . getmypid();
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir);
        }
        file_put_contents(self::$dir . '/router.php', <<<'PHP'
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/html') !== false) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html></html>';
    return;
}
if (strpos($path, '/missing') !== false) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo '{"error":"order not found","code":5}';
    return;
}
header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : null,
    'authorization' => isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null,
    'body' => file_get_contents('php://input'),
]);
PHP
        );

        self::$port = random_int(20000, 40000);
        $command = [PHP_BINARY, '-S', '127.0.0.1:' . self::$port, self::$dir . '/router.php'];
        self::$process = proc_open($command, [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);

        for ($i = 0; $i < 50; $i++) {
            $socket = @fsockopen('127.0.0.1', self::$port);
            if ($socket) {
                fclose($socket);
                return;
            }
            usleep(100000);
        }
        self::fail('the built-in web server did not start');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$process) {
            proc_terminate(self::$process);
            proc_close(self::$process);
        }
        @unlink(self::$dir . '/router.php');
        @rmdir(self::$dir);
    }

    private function api()
    {
        return new TapsilatAPI('test-key', 10, 'http://127.0.0.1:' . self::$port . '/api/v1');
    }

    public function testDeleteSendsItsJsonBody()
    {
        $echo = $this->api()->deleteOrderTerm(new OrderPaymentTermDeleteRequest('order-1', 't-1'));

        $this->assertSame('DELETE', $echo['method']);
        $this->assertSame('/api/v1/order/term', $echo['uri']);
        $this->assertSame('application/json', $echo['content_type']);
        $this->assertSame('Bearer test-key', $echo['authorization']);
        $this->assertSame(['order_id' => 'order-1', 'term_reference_id' => 't-1'], json_decode($echo['body'], true));
    }

    public function testPutSendsItsJsonBody()
    {
        $echo = $this->api()->updateSubmerchantPaymentItem(new SubmerchantPaymentItemUpdate([
            'locale' => 'tr', 'conversation_id' => 'c-1', 'payment_transaction_id' => 'tx-1',
            'sub_merchant_key' => 'key-1', 'sub_merchant_price' => '10.50', 'acquirer' => 'acq',
        ]));

        $this->assertSame('PUT', $echo['method']);
        $this->assertSame('10.50', json_decode($echo['body'], true)['sub_merchant_price']);
    }

    public function testBodilessPostUsesApiV2AndEscapesTheId()
    {
        $echo = $this->api()->reconcileMarketplacePayoutOperation('op/1');

        $this->assertSame('POST', $echo['method']);
        $this->assertSame('/api/v2/marketplace/payout-operations/op%2F1/reconcile', $echo['uri']);
        $this->assertSame('', $echo['body']);
    }

    public function testErrorStatusesThrowWithTheBodyDetails()
    {
        try {
            $this->api()->getOrder('missing');
            $this->fail('expected an APIException');
        } catch (APIException $e) {
            $this->assertSame(400, $e->statusCode);
            $this->assertSame(5, $e->code);
            $this->assertSame('order not found', $e->error);
        }
    }

    public function testAnHtmlPageThrows()
    {
        $this->expectException(APIException::class);
        $this->expectExceptionMessage('unexpected non-JSON response');
        $this->api()->getOrderStatus('html');
    }

    public function testDebugOutputDoesNotPrintTheApiKey()
    {
        $api = $this->api();
        $api->setDebug(true);
        ob_start();
        $api->getOrderStatus('ref-1');
        $output = ob_get_clean();

        $this->assertStringContainsString('GET http://127.0.0.1:' . self::$port . '/api/v1/order/ref-1/status', $output);
        $this->assertStringNotContainsString('test-key', $output);
    }
}
