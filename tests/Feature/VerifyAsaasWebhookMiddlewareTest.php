<?php

use Illuminate\Support\Facades\Route;
use LumenSistemas\Asaas\Http\Middleware\VerifyAsaasWebhook;

beforeEach(function (): void {
    config(['asaas.webhook_token' => 'my-secret-token']);

    Route::post('/_test/webhook', fn () => response()->json(['ok' => true]))
        ->middleware(VerifyAsaasWebhook::class);
});

describe('VerifyAsaasWebhook middleware', function (): void {
    it('allows a request with the correct token', function (): void {
        $this->withHeaders(['asaas-access-token' => 'my-secret-token'])
            ->postJson('/_test/webhook')
            ->assertOk()
            ->assertJson(['ok' => true]);
    });

    it('rejects a request with a wrong token', function (): void {
        $this->withHeaders(['asaas-access-token' => 'wrong-token'])
            ->postJson('/_test/webhook')
            ->assertUnauthorized();
    });

    it('rejects a request with no token header', function (): void {
        $this->postJson('/_test/webhook')
            ->assertUnauthorized();
    });

    it('rejects when webhook_token config is empty', function (): void {
        config(['asaas.webhook_token' => '']);

        $this->withHeaders(['asaas-access-token' => 'any-token'])
            ->postJson('/_test/webhook')
            ->assertUnauthorized();
    });
});
