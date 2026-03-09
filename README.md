# ASaaS Laravel Client

A typed PHP client for the [Asaas](https://www.asaas.com/) payment API (v3), built for Laravel 12.

## Requirements

- PHP 8.5+
- Laravel 12

## Installation

```bash
composer require lumensistemas/asaas-laravel
```

The service provider and `Asaas` facade are registered automatically via Laravel's package discovery.

Publish the config file:

```bash
php artisan vendor:publish --tag=asaas-config
```

## Configuration

Set your API key and environment in `.env`:

```env
ASAAS_API_KEY=your_api_key_here
ASAAS_ENVIRONMENT=sandbox   # or production
```

| Variable | Default | Description |
|---|---|---|
| `ASAAS_API_KEY` | _(empty)_ | Default API key |
| `ASAAS_ENVIRONMENT` | `sandbox` | `sandbox` or `production` |
| `ASAAS_WEBHOOK_TOKEN` | _(empty)_ | Token for incoming webhook verification |

## Usage

### Via facade

```php
use LumenSistemas\Asaas\Facades\Asaas;

// Customers
$customer = Asaas::customers()->find('cus_000000000001');
$customers = Asaas::customers()->list();

// Payments
$payment = Asaas::payments()->find('pay_000000000001');
$payments = Asaas::payments()->list();

// Webhooks
$webhooks = Asaas::webhooks()->list();
```

### Via dependency injection

```php
use LumenSistemas\Asaas\Asaas;

class MyService
{
    public function __construct(private readonly Asaas $asaas) {}

    public function handle(): void
    {
        $customer = $this->asaas->customers()->find('cus_000000000001');
    }
}
```

### Multi-tenant usage

`withApiKey()` returns a new immutable instance scoped to the given key — the singleton in the container is never mutated:

```php
$client = Asaas::withApiKey($tenant->asaas_api_key);

$customer = $client->customers()->create($data);
$payment  = $client->payments()->create($data);
```

## Resources

### Customers

```php
use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListFilters;
use LumenSistemas\Asaas\DTOs\Customer\UpdateCustomerData;
use LumenSistemas\Asaas\Facades\Asaas;

// List
$result = Asaas::customers()->list(new CustomerListFilters(
    name: 'John',
    limit: 20,
));

// Find
$customer = Asaas::customers()->find('cus_000000000001');

// Create
$customer = Asaas::customers()->create(new CreateCustomerData(
    name: 'John Doe',
    cpfCnpj: '24971563792',
    email: 'john@example.com',
    phone: '11999999999',
));

// Update
$customer = Asaas::customers()->update('cus_000000000001', new UpdateCustomerData(
    email: 'newemail@example.com',
));

// Delete / Restore
Asaas::customers()->delete('cus_000000000001');
Asaas::customers()->restore('cus_000000000001');
```

### Payments

```php
use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentDiscount;
use LumenSistemas\Asaas\DTOs\Payment\PaymentFine;
use LumenSistemas\Asaas\DTOs\Payment\PaymentInterest;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListFilters;
use LumenSistemas\Asaas\DTOs\Payment\UpdatePaymentData;
use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;
use LumenSistemas\Asaas\Enums\Payment\PaymentStatus;
use LumenSistemas\Asaas\Facades\Asaas;

// List with filters
$result = Asaas::payments()->list(new PaymentListFilters(
    customer: 'cus_000000000001',
    status: PaymentStatus::Pending,
    billingType: PaymentBillingType::Pix,
    dueDateGe: '2026-01-01',
    dueDateLe: '2026-12-31',
));

// Find
$payment = Asaas::payments()->find('pay_000000000001');

// Create
$payment = Asaas::payments()->create(new CreatePaymentData(
    customer: 'cus_000000000001',
    billingType: PaymentBillingType::Boleto,
    value: 199.90,
    dueDate: '2026-12-31',
    description: 'Order #1234',
    discount: new PaymentDiscount(value: 10.0, dueDateLimitDays: 5, type: 'PERCENTAGE'),
    fine: new PaymentFine(value: 2.0, type: 'PERCENTAGE'),
    interest: new PaymentInterest(value: 1.0),
));

// Update
$payment = Asaas::payments()->update('pay_000000000001', new UpdatePaymentData(
    value: 249.90,
    description: 'Updated order',
));

// Other actions
Asaas::payments()->delete('pay_000000000001');
Asaas::payments()->restore('pay_000000000001');
Asaas::payments()->refund('pay_000000000001', value: 50.0, description: 'Partial refund');
Asaas::payments()->receiveInCash('pay_000000000001', paymentDate: '2026-03-01');
$status = Asaas::payments()->getStatus('pay_000000000001');
$qr     = Asaas::payments()->getPixQrCode('pay_000000000001');
```

### Webhooks

```php
use LumenSistemas\Asaas\DTOs\Webhook\CreateWebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\UpdateWebhookData;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Enums\Webhook\WebhookSendType;
use LumenSistemas\Asaas\Facades\Asaas;

// List
$result = Asaas::webhooks()->list();

// Find
$webhook = Asaas::webhooks()->find('wbh_000000000001');

// Create
$webhook = Asaas::webhooks()->create(new CreateWebhookData(
    url: 'https://example.com/webhooks/asaas',
    events: [
        WebhookEvent::PaymentReceived,
        WebhookEvent::PaymentConfirmed,
        WebhookEvent::PaymentOverdue,
    ],
    name: 'Payment notifications',
    sendType: WebhookSendType::Sequentially,
    email: 'ops@example.com',
));

// Update
$webhook = Asaas::webhooks()->update('wbh_000000000001', new UpdateWebhookData(
    enabled: false,
));

// Delete
Asaas::webhooks()->delete('wbh_000000000001');

// Clear backoff penalty
Asaas::webhooks()->removeBackoff('wbh_000000000001');
```

## Handling incoming webhooks

### 1. Generate a token

Use `WebhookSignatureGenerator` to create a secure random token when registering a webhook:

```php
use LumenSistemas\Asaas\Webhook\WebhookSignatureGenerator;

$token = (new WebhookSignatureGenerator())->generate(); // 64-char hex string

$webhook = Asaas::webhooks()->create(new CreateWebhookData(
    url: 'https://example.com/webhooks/asaas',
    events: [WebhookEvent::PaymentReceived],
    authToken: $token,
));
```

Store `$token` in your `.env` as `ASAAS_WEBHOOK_TOKEN`.

### 2. Verify incoming requests

The `asaas.webhook` middleware reads `ASAAS_WEBHOOK_TOKEN` from config and rejects requests whose `asaas-access-token` header doesn't match (HTTP 401):

```php
// routes/api.php
Route::post('/webhooks/asaas', WebhookController::class)
    ->middleware('asaas.webhook');
```

Or verify manually:

```php
use LumenSistemas\Asaas\Webhook\WebhookSignatureVerifier;

$verifier = new WebhookSignatureVerifier();
if (! $verifier->verify($request->headers->all(), config('asaas.webhook_token'))) {
    abort(401);
}
```

### 3. Handle events

```php
use LumenSistemas\Asaas\DTOs\Webhook\WebhookEventPayload;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Webhook\WebhookHandler;

$payload = WebhookEventPayload::fromJson($request->getContent());

$handler = new WebhookHandler();
$handler
    ->on(WebhookEvent::PaymentReceived, function (WebhookEventPayload $p): void {
        // $p->payment is a typed PaymentData instance
    })
    ->on(WebhookEvent::PaymentOverdue, function (WebhookEventPayload $p): void {
        // handle overdue
    })
    ->onAny(function (WebhookEventPayload $p): void {
        // called for every event type
    });

$handler->handle($payload);
```

For payment events (`PAYMENT_*`), `$payload->payment` is a fully typed `PaymentData` instance. For all other event types it is `null`.

## Error handling

API errors (4xx/5xx responses) throw `AsaasApiException`:

```php
use LumenSistemas\Asaas\Exceptions\AsaasApiException;

try {
    $customer = Asaas::customers()->find('cus_invalid');
} catch (AsaasApiException $e) {
    $status = $e->status;          // int — HTTP status code
    $errors = $e->errors;          // array — error objects from the API body
}
```

## Testing

```bash
composer install
./vendor/bin/pest
```

To run integration tests against the real Asaas sandbox API:

```bash
export ASAAS_TEST_API_KEY="your_sandbox_key"
./vendor/bin/pest --testsuite=Integration
```

### Webhook integration tests

Webhook delivery tests require a public URL that Asaas can reach. Use [expose](https://expose.dev) (already in `require-dev`) to tunnel to the local test server.

**Step 1 — Start the webhook server** (in a separate terminal):

```bash
composer webhook:serve
# or with a custom port:
ASAAS_WEBHOOK_SERVER_PORT=9876 composer webhook:serve
```

**Step 2 — Expose it publicly** (in another terminal):

```bash
expose share http://localhost:9876
# Note the generated HTTPS URL, e.g. https://abc123.sharedwithexpose.com
```

**Step 3 — Export environment variables and run**:

```bash
export ASAAS_TEST_API_KEY="your_sandbox_key"
export ASAAS_WEBHOOK_URL="https://abc123.sharedwithexpose.com"
export ASAAS_WEBHOOK_TOKEN="your_secret_token"   # must match the token used when creating the webhook
./vendor/bin/pest --testsuite=Integration --filter=Webhook
```

The server records every incoming `POST` body to a temp directory. Tests poll `GET http://localhost:PORT/events` until the expected event arrives (up to 30 s). Cleanup is automatic via `afterAll`.

## License

MIT — see [LICENSE](LICENSE).
