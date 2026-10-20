# Tapsilat Client SDK for PHP

[![CI](https://github.com/tapsilat/tapsilat-php/workflows/CI/badge.svg)](https://github.com/tapsilat/tapsilat-php/actions?query=workflow%3ACI)
[![Packagist](https://img.shields.io/packagist/v/tapsilat/tapsilat-php.svg)](https://packagist.org/packages/tapsilat/tapsilat-php)
[![Packagist Downloads](https://img.shields.io/packagist/dt/tapsilat/tapsilat-php.svg)](https://packagist.org/packages/tapsilat/tapsilat-php)

Create orders and retrieve secure checkout URLs.

Requires PHP 7.4+

## Installation

You can install the package via Composer:

```bash
composer require tapsilat/tapsilat-php
```

or

```bash
git clone https://github.com/tapsilat/tapsilat-php.git
cd tapsilat-php
composer install
```

## Usage

### Environment Setup
Create a `.env` file:
```env
TAPSILAT_API_KEY=your_api_key_here
```

### TapsilatAPI Initialization
```php
<?php

require_once 'vendor/autoload.php';

use Tapsilat\TapsilatAPI;

$apiKey = $_ENV['TAPSILAT_API_KEY'];
$client = new TapsilatAPI($apiKey);
```

### Validators
The SDK includes built-in validators for common data types:

#### GSM Number Validation
```php
use Tapsilat\Validators;

// Valid formats
$validGsm = Validators::validateGsmNumber("+905551234567");  // International with +
$validGsm = Validators::validateGsmNumber("00905551234567"); // International with 00
$validGsm = Validators::validateGsmNumber("05551234567");    // National format
$validGsm = Validators::validateGsmNumber("5551234567");     // Local format

// Automatically cleans formatting
$cleanGsm = Validators::validateGsmNumber("+90 555 123-45-67"); // Returns: "+905551234567"

// Throws APIException for invalid formats
try {
    Validators::validateGsmNumber("invalid-phone");
} catch (APIException $e) {
    echo "Error: " . $e->error;
}
```

#### Installments Validation
```php
use Tapsilat\Validators;

// Valid installment strings
$installments = Validators::validateInstallments("1,2,3,6");     // Returns: [1, 2, 3, 6]
$installments = Validators::validateInstallments("1, 2, 3, 6"); // Handles spaces
$installments = Validators::validateInstallments("");           // Returns: [1] (default)

// Throws APIException for invalid values
try {
    Validators::validateInstallments("1,15,abc"); // 15 > 12, abc is not a number
} catch (APIException $e) {
    echo "Error: " . $e->error;
}
```

### Order Create Process
```php
use Tapsilat\Models\BuyerDTO;
use Tapsilat\Models\OrderCreateRequest;

// GSM number will be automatically validated in createOrder
$buyer = new BuyerDTO(
    "John",
    "Doe",
    null, // birth_date
    null, // city
    null, // country
    "test@example.com", // email
    "+90 555 123-45-67" // Will be cleaned automatically
);
$order = new OrderCreateRequest(100, "TRY", "tr", $buyer);

$orderResponse = $client->createOrder($order);
```

### Get Order Details
```php
$referenceId = "mock-uuid-reference-id";
$orderDetails = $client->getOrder($referenceId);
```

### Get Order Details by Conversation ID
```php
$conversationId = "mock-uuid-conversation-id";
$orderDetails = $client->getOrderByConversationId($conversationId);
```

### Get Order List
```php
$orderList = $client->getOrders($page = 1, $perPage = 5, $buyerId = "buy_1", $status = 1);
```

### Get Order Submerchants
```php
$orderList = $client->getOrderSubmerchants($page = 1, $perPage = 5);
```

### Get Checkout URL
```php
$referenceId = "mock-uuid-reference-id";
$checkoutUrl = $client->getCheckoutUrl($referenceId);
```

### Order Cancel Process
```php
$referenceId = "mock-uuid-reference-id";
$client->cancelOrder($referenceId);
```

### Order Refund Process
```php
use Tapsilat\Models\RefundOrderRequest;

$refundData = new RefundOrderRequest(100, "mock-uuid-reference-id");
$client->refundOrder($refundData);
```

### Order Refund All Process
```php
$referenceId = "mock-uuid-reference-id";
$client->refundAllOrder($referenceId);
```

### Get Order Payment Details
```php
$referenceId = "mock-uuid-reference-id";
$client->getOrderPaymentDetails($referenceId);

// You can get with conversation_id too
$conversationId = "mock-uuid-conversation-id";
$client->getOrderPaymentDetails($referenceId, $conversationId);
```

### Get Order Status
```php
$referenceId = "mock-uuid-reference-id";
$client->getOrderStatus($referenceId);
```

### Get Order Transactions
```php
$referenceId = "mock-uuid-reference-id";
$client->getOrderTransactions($referenceId);
```

### Charge Order
```php
use Tapsilat\Models\OrderChargeRequest;

$chargeData = new OrderChargeRequest("mock-uuid-reference-id");
$result = $client->chargeOrder($chargeData);
if (empty($result['isSuccess'])) {
    // The request can succeed while the charge fails; the charge response keys are camelCase
    echo $result['errorCode'] . ': ' . $result['message'];
}
```

### Get Order Payments
`getAllOrdersPayments()` is deprecated: the API has no route for it. List one order's payments instead:
```php
use Tapsilat\Models\GetOrderPaymentsRequest;

// Arguments: order ID, conversation ID, order reference ID; set one of them
$payments = $client->getOrderPayments(new GetOrderPaymentsRequest(null, null, "mock-uuid-reference-id"));
```

### Get Order Term
```php
$termReferenceId = "mock-uuid-term-reference-id";
$client->getOrderTerm($termReferenceId);
```

### Create Order Term
`order_id` is the order's ID (from order creation), not its reference ID. Term dates use `YYYY-MM-DD`.
```php
use Tapsilat\Models\OrderPaymentTermCreateRequest;

$orderId = "mock-order-id";
$terms = [
    new OrderPaymentTermCreateRequest(
        $orderId,
        "TERM-123000456",
        5000,
        "2025-10-10",
        1,
        true,
        "PENDING"
    ),
    new OrderPaymentTermCreateRequest(
        $orderId,
        "TERM-123000457",
        5000,
        "2025-11-10",
        2,
        true,
        "PENDING"
    )
];

foreach ($terms as $term) {
    $client->createOrderTerm($term);
}
```

### Delete Order Term
```php
use Tapsilat\Models\OrderPaymentTermDeleteRequest;

$client->deleteOrderTerm(new OrderPaymentTermDeleteRequest("mock-uuid-order-id", "TERM-123000456"));
```

### Update Order Term
An update replaces the term, so send every field.
```php
use Tapsilat\Models\OrderPaymentTermUpdateRequest;

$term = new OrderPaymentTermUpdateRequest(
    "TERM-123000457",
    null, // amount
    "2025-12-10", // due_date
    null, // paid_date
    true  // required
);
$client->updateOrderTerm($term);
```

### Refund Order Term
```php
use Tapsilat\Models\OrderTermRefundRequest;

// term_id is the term's ID; the order's reference ID is required
$term = new OrderTermRefundRequest("term-id", 100, "mock-uuid-reference-id");
$client->refundOrderTerm($term);
```

### Terminate Order
```php
$client->terminateOrder("mock-uuid-reference-id");
```

### Manual Callback for Order
```php
$referenceId = "mock-uuid-reference-id";
$conversationId = "mock-uuid-conversation-id";
$client->manualCallback($referenceId, $conversationId);
```

### Order Related Reference Update
```php
$referenceId = "mock-uuid-reference-id";
$relatedReferenceId = "mock-uuid-related-reference-id";
$client->relatedUpdate($referenceId, ["related_reference_id" => $relatedReferenceId]);
```

### Get Organization Settings
```php
$settings = $client->getOrganizationSettings();
```

### Organization Management
```php
use Tapsilat\Models\CreateOrganizationCurrencyPayload;

// Create Currency
$currencyPayload = new CreateOrganizationCurrencyPayload("EUR");
$client->createOrganizationCurrency($currencyPayload);

// Get Organization Partners
$partners = $client->getOrganizationPartners();

// Get Organization Limits by ID
$limits = $client->getOrganizationLimitsById("mock-org-uuid", ["currency" => "TRY"]);

// Most endpoints accept currency units directly; resolve one to its UUID where a UUID is required
$currencyId = $client->resolveCurrencyId("TRY");
```

`getOrganizationLimits()`, `getOrganizationLimitUser()` and `setOrganizationLimitUser()` are deprecated: the API has no such routes.

### Health Monitoring
```php
$health = $client->healthCheck();
echo "Status: " . $health['status']; // "OK"
```


### Submerchant Management
```php
use Tapsilat\Models\SubmerchantCreateDTO;

$submerchantCreate = new SubmerchantCreateDTO(
    "Address", "City", "Country", "email@example.com", "5555555555",
    "TR123", "12345678901", "Name", "Contact", "Surname", "PERSONAL",
    "Tax Office", "34000"
);
$response = $client->createSubmerchant($submerchantCreate);

$submerchants = $client->listSubmerchants();
```

### Document Export (PDF & Excel)
```php
// Returns raw PDF blob
$pdfData = $client->getOrderPdf("mock-uuid-reference-id");
// file_put_contents("order.pdf", $pdfData);

// Returns raw Excel blob
$excelData = $client->getOrderExcel("mock-uuid-reference-id");
// file_put_contents("order.xlsx", $excelData);
```

### Card Installments
```php
use Tapsilat\Models\CardInstallmentsRequest;
use Tapsilat\Models\InstallmentTableRequest;

$options = $client->getCardInstallments(new CardInstallmentsRequest([
    'bin' => '54066200', 'amount' => 1000, 'currency' => 'TRY', 'is_three_d' => true,
]));
$table = $client->getInstallmentTable(new InstallmentTableRequest(['amount' => 1000, 'currency' => 'TRY']));
```

### Submerchant Settlement and Payout Events
```php
use Tapsilat\Models\SubmerchantPaymentAction;
use Tapsilat\Models\SubmerchantPaymentItemUpdate;
use Tapsilat\Models\SubmerchantPayoutEventRequest;

$action = new SubmerchantPaymentAction([
    'locale' => 'tr', 'conversation_id' => 'conv-1', 'payment_transaction_id' => 'tx-1', 'acquirer' => 'acquirer',
]);
$client->approveSubmerchantPayment($action);
$client->disapproveSubmerchantPayment($action);

$client->updateSubmerchantPaymentItem(new SubmerchantPaymentItemUpdate([
    'locale' => 'tr', 'conversation_id' => 'conv-1', 'payment_transaction_id' => 'tx-1',
    'sub_merchant_key' => 'key-1', 'sub_merchant_price' => '60.25', 'acquirer' => 'acquirer',
]));

$client->recordSubmerchantPayoutEvent(new SubmerchantPayoutEventRequest([
    'idempotency_key' => 'event-1', 'order_reference' => 'reference-id', 'item_id' => 'item-1', 'event_type' => 'DELIVERED',
]));
```

### Virtual POS
A VPOS update replaces the whole configuration (fields left out are stored as empty or false), so read it first
and send every field back.
```php
use Tapsilat\Models\VposUpdateRequest;

$vpos = $client->getVpos('vpos-id');
$fields = array_intersect_key($vpos, get_class_vars(VposUpdateRequest::class));
$client->updateVpos('vpos-id', new VposUpdateRequest(array_merge($fields, ['failover' => true])));

$list = $client->listVpos(1, 10);
$acquirers = $client->listVposAcquirers();
$submerchants = $client->listVposSubmerchants(1, 10, 'vpos-id');
```

### Saved Cards
```php
use Tapsilat\Models\CardTokenizeRequest;

$result = $client->tokenizeCard(new CardTokenizeRequest([
    'card_number' => '4111111111111111', 'holder_name' => 'John Doe',
    'expiry_month' => '12', 'expiry_year' => '30', 'cvv' => '123',
]));
$cards = $client->listSavedCards(1, 10);
$client->deleteSavedCard('card-id');
```

### Marketplace (API v2)
Marketplace calls use the `/api/v2` URL derived from the base URL. Seller and payout revisions are opaque strings:
send them back exactly as received.
```php
use Tapsilat\Models\MarketplaceApprovalPolicyInput;
use Tapsilat\Models\MarketplacePayoutApproval;
use Tapsilat\Models\MarketplacePayoutFilter;
use Tapsilat\Models\MarketplaceProfileUpdate;

$seller = $client->getMarketplaceSubmerchant('seller-id');
$client->updateMarketplaceSubmerchant('seller-id', new MarketplaceProfileUpdate([
    'name' => 'New name', 'revision' => $seller['revision'],
]));

$payouts = $client->listMarketplacePayouts(new MarketplacePayoutFilter(['status' => 'WAITING_APPROVAL', 'page' => 1]));
$client->approveMarketplacePayouts(new MarketplacePayoutApproval([
    'ids' => array_column($payouts['rows'], 'id'),
]));

// The approval policy currency must be a UUID; a unit such as "TRY" is resolved for you
$client->createMarketplaceApprovalPolicy(new MarketplaceApprovalPolicyInput([
    'name' => 'Large orders', 'priority' => 1, 'min_amount' => '1000', 'currency_id' => 'TRY', 'mode' => 'manual',
]));
```

### Error Handling
API errors throw `Tapsilat\APIException` with the HTTP status (`statusCode`), the body's `code`, the message (`error`,
from the body's `message` or `error`), per-field `errors`, the body's `status` and the raw body (`rawBody`). A response
that is not JSON, such as an HTML page from a wrong base URL, also throws.
```php
use Tapsilat\APIException;

try {
    $client->cancelOrder(new CancelOrderRequest("mock-uuid-reference-id"));
} catch (APIException $e) {
    echo $e->statusCode . ' [' . $e->code . '] ' . $e->error;
    print_r($e->errors);
}
```

## System & Webhooks

### Get System Definitions and Statuses
```php
$orderStatuses = $client->getSystemOrderStatuses();
$basketItemTypes = $client->getSystemBasketItemTypes();
$errorCodes = $client->getSystemErrorCodes();
$paymentTermStatuses = $client->getSystemPaymentTermStatuses();
$productTypes = $client->getSystemProductTypes();
$shortcutTypes = $client->getSystemShortcutTypes();
$transactionPaymentTypes = $client->getSystemTransactionPaymentTypes();
$transactionPurposes = $client->getSystemTransactionPurposes();
$transactionStatuses = $client->getSystemTransactionStatuses();
```

### Get System Config
```php
$config = $client->getSystemConfig();
```

### Webhook Handling

#### Verify Webhook Signature
The signature is a hex HMAC-SHA256 of the raw payload, with or without the `sha256=` prefix, compared in constant time.
```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_TAPSILAT_SIGNATURE'];
$secret = 'your_webhook_secret';

$isValid = TapsilatAPI::verifyWebhook($payload, $signature, $secret);

if ($isValid) {
    // Process webhook
    $event = json_decode($payload, true);
    // ...
} else {
    // Invalid signature
    http_response_code(401);
    exit;
}
```

## Subscription Operations

### Create Subscription
```php
use Tapsilat\Models\SubscriptionCreateRequest;
use Tapsilat\Models\SubscriptionBillingDTO;
use Tapsilat\Models\SubscriptionUserDTO;

$billing = new SubscriptionBillingDTO(
    "123 Main St",      // address
    "Istanbul",         // city
    "John Doe",         // contact_name
    "TR",              // country
    "1234567890",      // vat_number
    "34000"            // zip_code
);

$user = new SubscriptionUserDTO(
    "user_123",           // id
    "John",               // first_name
    "Doe",                // last_name
    "john@example.com",   // email
    "5551234567",         // phone
    "12345678901",        // identity_number
    "123 Main St",        // address
    "Istanbul",           // city
    "TR",                 // country
    "34000"               // zip_code
);

$subscription = new SubscriptionCreateRequest(
    100.0,                    // amount
    "TRY",                    // currency
    "Monthly Subscription",   // title
    30,                       // period (in days)
    1,                        // cycle
    1,                        // payment_date (day of month)
    "ext_sub_123",            // external_reference_id
    "https://example.com/success",  // success_url
    "https://example.com/failure",  // failure_url
    "card_token_123",         // card_id
    $billing,                 // billing
    $user                     // user
);

$response = $client->createSubscription($subscription);
echo "Subscription Reference ID: " . $response->getReferenceId();
echo "Order Reference ID: " . $response->getOrderReferenceId();
```

### Get Subscription
```php
use Tapsilat\Models\SubscriptionGetRequest;

// Get by reference_id
$request = new SubscriptionGetRequest("subscription_reference_id", null);
$subscription = $client->getSubscription($request);

// Or get by external_reference_id
$request = new SubscriptionGetRequest(null, "ext_sub_123");
$subscription = $client->getSubscription($request);

echo "Title: " . $subscription->getTitle();
echo "Amount: " . $subscription->getAmount();
echo "Is Active: " . ($subscription->getIsActive() ? 'Yes' : 'No');
echo "Payment Status: " . $subscription->getPaymentStatus();
```

### List Subscriptions
```php
// Get first page with 10 items per page
$subscriptions = $client->listSubscriptions(1, 10);

echo "Total: " . $subscriptions['total'];
echo "Total Pages: " . $subscriptions['total_pages'];

foreach ($subscriptions['rows'] as $subscription) {
    echo "Subscription: " . $subscription['title'] . " - " . $subscription['amount'];
}
```

### Cancel Subscription
```php
use Tapsilat\Models\SubscriptionCancelRequest;

// Cancel by reference_id
$request = new SubscriptionCancelRequest("subscription_reference_id", null);
$client->cancelSubscription($request);

// Or cancel by external_reference_id
$request = new SubscriptionCancelRequest(null, "ext_sub_123");
$client->cancelSubscription($request);
```

### Redirect Subscription
```php
use Tapsilat\Models\SubscriptionRedirectRequest;

$request = new SubscriptionRedirectRequest("subscription_id");
$response = $client->redirectSubscription($request);
echo "Redirect URL: " . $response->getUrl();
```

## Requirements

- PHP >= 7.4

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Testing

The SDK includes comprehensive unit and integration tests.

### Environment Setup

For integration tests, create a `.env` file in the project root:

```env
TAPSILAT_API_KEY=your_api_key_here
```

### Running Tests

```bash
# Run all tests (unit + integration)
composer test
# or
vendor/bin/phpunit

# Run only unit tests (no API calls)
vendor/bin/phpunit --testsuite=Unit

# Run only integration tests (real API calls)
vendor/bin/phpunit --testsuite=Integration

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### Test Structure

```
tests/
├── Unit/                    # Mock-based tests (no API calls)
│   ├── OrderTest.php       # Order operations
│   ├── SubscriptionTest.php # Subscription operations
│   ├── OrganizationTest.php # Organization settings
│   └── ValidatorTest.php   # Utility validators
└── Integration/            # Real API tests (requires API key)
    ├── OrderIntegrationTest.php
    └── SubscriptionIntegrationTest.php
```

### Test Coverage

- **Unit Tests**: 87 tests covering all 28 API methods
- **Integration Tests**: 6 tests for critical API flows
- **Total**: 93 tests with 228 assertions


## CI/CD

This package uses GitHub Actions for continuous integration and deployment:

- **CI**: Runs on every push to main/develop and pull requests
  - Tests against PHP 7.4, 8.0, 8.1, 8.2, and 8.3
  - Validates composer.json
  - Runs PHPUnit tests
  - Checks code style with PHP_CodeSniffer

- **Release & Publish**: Automatically triggered when a new tag is pushed
  - Creates a GitHub release (if it doesn't exist)
  - Validates and tests the package
  - Publishes to Packagist (if configured)
  - Verifies package availability on Packagist

- **Packagist Webhook**: Automatically notifies Packagist on every push to main
  - Triggers on pushes to main branch and merged PRs
  - Keeps Packagist updated with latest changes
  - Works alongside GitHub webhook for immediate updates

- **Dependabot**: Automatically updates dependencies
  - Weekly updates for Composer packages
  - Weekly updates for GitHub Actions
  - Creates pull requests for review

To create a new release:

```bash
git tag v1.0.0
git push origin v1.0.0
```

This will automatically:
1. Create a GitHub release
2. Publish the package to Packagist
3. Make it available via `composer require tapsilat/tapsilat-php`

## Docker Development

This package includes Docker support for easy development setup.

### Quick Start

```bash
# Build the Docker image
./docker-dev.sh build

# Start the development container
./docker-dev.sh start

# Open a shell in the container
./docker-dev.sh shell

# Run tests
./docker-dev.sh test
```

### Available Commands

- `./docker-dev.sh build` - Build the Docker image
- `./docker-dev.sh start` - Start the development container
- `./docker-dev.sh stop` - Stop the development container
- `./docker-dev.sh restart` - Restart the development container
- `./docker-dev.sh shell` - Open a shell in the development container
- `./docker-dev.sh test` - Run tests in the container
- `./docker-dev.sh install` - Install dependencies
- `./docker-dev.sh clean` - Clean up containers and images
- `./docker-dev.sh help` - Show help message

### Manual Docker Commands

```bash
# Build and start with docker-compose
docker-compose up -d tapsilat-dev

# Run tests
docker-compose run --rm tapsilat-test

# Execute commands in the container
docker-compose exec tapsilat-dev composer install
docker-compose exec tapsilat-dev composer test
docker-compose exec tapsilat-dev php example.php
```
