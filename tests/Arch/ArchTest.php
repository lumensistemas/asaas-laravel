<?php

arch()->preset()->php();

arch()->preset()->security();

// ──────────────────────────────────────────────────────────────
// DTOs
// ──────────────────────────────────────────────────────────────

arch('DTOs are readonly classes')
    ->expect('LumenSistemas\Asaas\DTOs')
    ->toBeReadonly();

arch('DTOs have no public setters')
    ->expect('LumenSistemas\Asaas\DTOs')
    ->not->toHavePublicMethodsBesides(['__construct', 'toArray', 'fromArray']);

arch('DTOs do not depend on Laravel or the HTTP client')
    ->expect('LumenSistemas\Asaas\DTOs')
    ->not->toUse([
        Illuminate\Http\Client\Response::class,
        Illuminate\Support\Facades\Http::class,
    ]);

// ──────────────────────────────────────────────────────────────
// Services
// ──────────────────────────────────────────────────────────────

arch('Services depend on the interface, not the concrete client')
    ->expect('LumenSistemas\Asaas\Services')
    ->not->toUse(LumenSistemas\Asaas\AsaasClient::class);

arch('Services do not use facades')
    ->expect('LumenSistemas\Asaas\Services')
    ->not->toUse('Illuminate\Support\Facades');

// ──────────────────────────────────────────────────────────────
// Exceptions
// ──────────────────────────────────────────────────────────────

arch('All exceptions extend AsaasException')
    ->expect('LumenSistemas\Asaas\Exceptions')
    ->toExtend(LumenSistemas\Asaas\Exceptions\AsaasException::class);

arch('AsaasException extends RuntimeException')
    ->expect(LumenSistemas\Asaas\Exceptions\AsaasException::class)
    ->toExtend('RuntimeException');

// ──────────────────────────────────────────────────────────────
// Contracts
// ──────────────────────────────────────────────────────────────

arch('Contracts namespace contains only interfaces')
    ->expect('LumenSistemas\Asaas\Contracts')
    ->toBeInterface();

arch('AsaasClient implements the client interface')
    ->expect(LumenSistemas\Asaas\AsaasClient::class)
    ->toImplement(LumenSistemas\Asaas\Contracts\AsaasClientInterface::class);

// ──────────────────────────────────────────────────────────────
// Facades
// ──────────────────────────────────────────────────────────────

arch('Facades extend the Laravel Facade base class')
    ->expect('LumenSistemas\Asaas\Facades')
    ->toExtend(Illuminate\Support\Facades\Facade::class);

// ──────────────────────────────────────────────────────────────
// Enums
// ──────────────────────────────────────────────────────────────

arch('AsaasEnvironment is a backed enum')
    ->expect(LumenSistemas\Asaas\AsaasEnvironment::class)
    ->toBeEnum();

// ──────────────────────────────────────────────────────────────
// Global rules
// ──────────────────────────────────────────────────────────────

arch('Source code does not use dd, dump, var_dump, or ray')
    ->expect('LumenSistemas\Asaas')
    ->not->toUse(['dd', 'dump', 'var_dump', 'ray']);

arch('Source code uses strict types everywhere')
    ->expect('LumenSistemas\Asaas')
    ->toUseStrictTypes();
