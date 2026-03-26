<?php

use LumenSistemas\Asaas\Webhook\WebhookSignatureGenerator;
use LumenSistemas\Asaas\Webhook\WebhookSignatureVerifier;

describe('WebhookSignatureVerifier::verify()', function (): void {
    it('returns true when the header token matches', function (): void {
        $verifier = new WebhookSignatureVerifier();

        $result = $verifier->verify(
            ['asaas-access-token' => 'secret-token'],
            'secret-token',
        );

        expect($result)->toBeTrue();
    });

    it('returns false when the header token does not match', function (): void {
        $verifier = new WebhookSignatureVerifier();

        $result = $verifier->verify(
            ['asaas-access-token' => 'wrong-token'],
            'secret-token',
        );

        expect($result)->toBeFalse();
    });

    it('returns false when the header is missing', function (): void {
        $verifier = new WebhookSignatureVerifier();

        $result = $verifier->verify(
            ['content-type' => 'application/json'],
            'secret-token',
        );

        expect($result)->toBeFalse();
    });

    it('is case-insensitive for the header name', function (): void {
        $verifier = new WebhookSignatureVerifier();

        $result = $verifier->verify(
            ['Asaas-Access-Token' => 'secret-token'],
            'secret-token',
        );

        expect($result)->toBeTrue();
    });

    it('accepts a header value as an array (Laravel headers->all() shape)', function (): void {
        $verifier = new WebhookSignatureVerifier();

        $result = $verifier->verify(
            ['asaas-access-token' => ['secret-token']],
            'secret-token',
        );

        expect($result)->toBeTrue();
    });

    it('returns false when expected token is empty', function (): void {
        $verifier = new WebhookSignatureVerifier();

        $result = $verifier->verify(
            ['asaas-access-token' => 'secret-token'],
            '',
        );

        expect($result)->toBeFalse();
    });
});

describe('WebhookSignatureGenerator::generate()', function (): void {
    it('returns a hex string of the expected length', function (): void {
        $generator = new WebhookSignatureGenerator();
        $token = $generator->generate();

        expect($token)->toBeString()
            ->and(mb_strlen($token))->toBe(64); // 32 bytes → 64 hex chars
    });

    it('respects a custom byte count', function (): void {
        $generator = new WebhookSignatureGenerator();
        $token = $generator->generate(16);

        expect(mb_strlen($token))->toBe(32); // 16 bytes → 32 hex chars
    });

    it('produces different tokens on each call', function (): void {
        $generator = new WebhookSignatureGenerator();

        expect($generator->generate())->not->toBe($generator->generate());
    });

    it('returns only hexadecimal characters', function (): void {
        $generator = new WebhookSignatureGenerator();

        expect($generator->generate())->toMatch('/^[0-9a-f]+$/');
    });
});
