# CLAUDE.md — asaas-laravel

Developer guidelines and architecture reference for this package.

## Package overview

`lumensistemas/asaas-laravel` is a Laravel package that provides a typed PHP client for the [Asaas](https://www.asaas.com/) payment API (v3).
It is built for multi-tenant applications where each tenant may have a distinct API key injected at runtime.

---

## Directory structure

```
src/
  Asaas.php                  # Main entry point / manager class
  AsaasClient.php            # HTTP client (wraps Laravel Http)
  AsaasEnvironment.php       # Enum: Production | Sandbox (owns base URLs)
  AsaasServiceProvider.php   # Laravel service provider
  Contracts/
    AsaasClientInterface.php # Interface for the HTTP client
  DTOs/
    Customer/
      CustomerData.php           # Response DTO (read)
      CreateCustomerData.php     # Request DTO for POST
      UpdateCustomerData.php     # Request DTO for PUT
      CustomerListFilters.php    # Query parameters for list
      CustomerListResult.php     # Paginated list response DTO
  Exceptions/
    AsaasException.php         # Base exception
    AsaasApiException.php      # API error (4xx/5xx) with $errors array
  Facades/
    Asaas.php                  # Laravel facade
  Services/
    CustomerService.php       # Methods for /v3/customers
config/
  asaas.php                   # Published config file
tests/
  Feature/                    # Integration-style tests (with mock HTTP)
  Unit/                       # Unit tests for DTOs, etc.
```

---

## Architecture

### Core concepts

**`AsaasEnvironment`** — a backed enum (`'production'` | `'sandbox'`) that owns both base URLs. Config and the service provider use it instead of raw strings.

**`AsaasClient`** — the low-level HTTP adapter.
- Uses Laravel's `Http` client; never imports Guzzle directly.
- Is **immutable by design**: `withApiKey()` and `withHeaders()` each return a cloned instance, leaving the original untouched.
- Sends a default `User-Agent` header (`lumensistemas/asaas-laravel`); callers can override or extend headers via `withHeaders()`.
- Bound in the container as `AsaasClientInterface::class` (singleton).

**`Asaas`** — the main façade/manager.
- Bound in the container as `Asaas::class` (singleton).
- Exposes service accessors (`customers()`, etc.).
- `withApiKey(string $key)` returns a **new `Asaas` instance** wrapping a cloned client with that key — ideal for per-tenant use.

**Service classes** (e.g. `CustomerService`) — one class per API resource.
- Accept and return **DTOs**, never raw arrays.
- Depend only on `AsaasClientInterface`, not on `AsaasClient` directly.

**DTOs** — plain readonly PHP classes.
- Request DTOs expose a `toArray(): array` method that produces the API payload (strips nulls).
- Response DTOs expose a `static fromArray(array $data): self` named constructor.

**Exceptions**
- All exceptions extend `AsaasException`.
- `AsaasApiException` is thrown for HTTP 4xx/5xx responses; it carries an `Illuminate\Http\Client\Response` and the parsed `$errors` array from the API body.

---

## Adding a new resource

1. **Research the API** using the MCP server tools (`mcp__asaas__search-endpoints`, `mcp__asaas__get-endpoint`, `mcp__asaas__get-request-body`, `mcp__asaas__get-response-schema`) before writing any code.
2. Create DTOs in `src/DTOs/{Resource}/`:
   - `{Resource}Data.php` — response shape
   - `Create{Resource}Data.php` — POST payload
   - `Update{Resource}Data.php` — PUT payload (all fields optional)
   - `{Resource}ListFilters.php` — query params
   - `{Resource}ListResult.php` — paginated wrapper
3. Create `src/Services/{Resource}Service.php` implementing the CRUD methods.
4. Add a `{resource}(): {Resource}Service` accessor to `Asaas.php`.
5. Add the method to the `@method` docblock in `Facades/Asaas.php`.
6. Write tests in `tests/Feature/` and `tests/Unit/`.

---

## Multi-tenant usage

The default API key is resolved from config (`asaas.api_key` / `ASAAS_API_KEY`).
For per-tenant keys, inject the key at runtime — `withApiKey` is **non-destructive**:

```php
// Using the facade
$customer = Asaas::withApiKey($tenant->asaas_api_key)
    ->customers()
    ->find($customerId);

// Using dependency injection
public function __construct(private readonly \LumenSistemas\Asaas\Asaas $asaas) {}

public function handle(Tenant $tenant): void
{
    $client = $this->asaas->withApiKey($tenant->asaas_api_key);
    $customer = $client->customers()->create($data);
}
```

The singleton registered in the container is **never mutated**; `withApiKey` clones both the `Asaas` instance and the underlying `AsaasClient`.

---

## Conventions

| Concern | Rule |
|---|---|
| PHP version | Require PHP 8.5+ (readonly properties, named args, intersection types) |
| Laravel versions | Support 12 |
| Typing | All public APIs must be fully typed; no `mixed` in public signatures unless unavoidable |
| DTOs | Always readonly; no setters; use named constructor `fromArray` for deserialization |
| Nullability | Nullable fields use `?type`; never use `mixed` to paper over optionality |
| API payloads | Strip `null` values before sending (use `array_filter` in `toArray()`) |
| Errors | Check `$response->failed()`, read `$response->json('errors')`, throw `AsaasApiException` |
| Http client | Use `->asJson()` for POST/PUT body; pass query array as second arg to `->get()` |
| Tests | Use `Http::fake()` — never hit the real API in tests |
| Config publish tag | `asaas-config` |
| Facade alias | `Asaas` |

---

## Environment variables

| Variable | Default | Description |
|---|---|---|
| `ASAAS_API_KEY` | _(empty)_ | Default API key |
| `ASAAS_ENVIRONMENT` | `sandbox` | `sandbox` or `production` |

---

## Running tests

```bash
composer install
./vendor/bin/pest
```

---

## API reference

Base URLs:
- Sandbox: `https://api-sandbox.asaas.com`
- Production: `https://api.asaas.com`

Authentication: HTTP header `access_token: $YOUR_API_KEY`

Explore available endpoints using the MCP server:

```
mcp__asaas__list-specs
mcp__asaas__search-endpoints  { pattern: "payment" }
mcp__asaas__get-endpoint      { title, path, method }
mcp__asaas__get-request-body  { title, path, method }
mcp__asaas__get-response-schema { title, path, method, statusCode }
```
