<?php

use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\AsaasClient;
use LumenSistemas\Asaas\AsaasEnvironment;
use LumenSistemas\Asaas\Exceptions\AsaasApiException;

function sandboxClient(string $apiKey = 'test_key'): AsaasClient
{
    return new AsaasClient(
        environment: AsaasEnvironment::Sandbox,
        defaultApiKey: $apiKey,
    );
}

describe('AsaasClient', function (): void {
    it('sends the access_token header on every request', function (): void {
        Http::fake(['*' => Http::response(['id' => 'cus_1'])]);

        sandboxClient('my_secret_key')->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => $request->header('access_token')[0] === 'my_secret_key');
    });

    it('sends the default User-Agent header', function (): void {
        Http::fake(['*' => Http::response(['id' => 'cus_1'])]);

        sandboxClient()->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => str_contains(
            $request->header('User-Agent')[0] ?? '',
            'lumensistemas/asaas-laravel'
        ));
    });

    it('uses the key from withApiKey() instead of the default', function (): void {
        Http::fake(['*' => Http::response(['id' => 'cus_1'])]);

        sandboxClient('default_key')->withApiKey('tenant_key')->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => $request->header('access_token')[0] === 'tenant_key');
    });

    it('does not mutate the original instance when calling withApiKey()', function (): void {
        Http::fake(['*' => Http::response(['id' => 'cus_1'])]);

        $original = sandboxClient('original_key');
        $original->withApiKey('new_key');

        $original->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => $request->header('access_token')[0] === 'original_key');
    });

    it('merges extra headers set via withHeaders()', function (): void {
        Http::fake(['*' => Http::response(['id' => 'cus_1'])]);

        sandboxClient()->withHeaders(['X-Custom' => 'abc'])->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => $request->header('X-Custom')[0] === 'abc');
    });

    it('does not mutate the original instance when calling withHeaders()', function (): void {
        Http::fake(['*' => Http::response(['id' => 'cus_1'])]);

        $original = sandboxClient();
        $original->withHeaders(['X-Custom' => 'abc']);

        $original->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => empty($request->header('X-Custom')));
    });

    it('hits the sandbox base URL', function (): void {
        Http::fake(['api-sandbox.asaas.com/*' => Http::response(['id' => 'cus_1'])]);

        sandboxClient()->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'api-sandbox.asaas.com'));
    });

    it('hits the production base URL when environment is production', function (): void {
        Http::fake(['api.asaas.com/*' => Http::response(['id' => 'cus_1'])]);

        $client = new AsaasClient(AsaasEnvironment::Production, 'key');
        $client->get('/v3/customers/cus_1');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'api.asaas.com'));
    });

    it('throws AsaasApiException on a 4xx response', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_cpfCnpj', 'description' => 'CPF/CNPJ inválido']]],
            422
        )]);

        sandboxClient()->post('/v3/customers', ['name' => 'X', 'cpfCnpj' => '000']);
    })->throws(AsaasApiException::class);

    it('exposes errors and status code from AsaasApiException', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_access_token', 'description' => 'Token inválido']]],
            401
        )]);

        try {
            sandboxClient()->get('/v3/customers');
        } catch (AsaasApiException $e) {
            expect($e->getStatusCode())->toBe(401)
                ->and($e->getErrors())->toHaveCount(1)
                ->and($e->getErrors()[0]['code'])->toBe('invalid_access_token');
        }
    });

    it('returns null for empty response bodies', function (): void {
        Http::fake(['*' => Http::response('', 200)]);

        $result = sandboxClient()->delete('/v3/customers/cus_1');

        expect($result)->toBeNull();
    });
});
