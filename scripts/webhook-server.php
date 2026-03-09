<?php

/**
 * Asaas Webhook Integration Test Server
 *
 * A lightweight PHP built-in server script that records incoming webhook
 * payloads to disk so integration tests can assert on them.
 *
 * Usage:
 *   php -S localhost:9876 scripts/webhook-server.php
 *
 * Or via composer:
 *   composer webhook:serve
 *
 * Endpoints:
 *   POST   /           — Record an incoming Asaas webhook payload (responds 200)
 *   GET    /events     — Return all recorded payloads as a JSON array
 *   DELETE /events     — Clear all recorded payloads (returns 204)
 *   GET    /health     — Returns {"ok":true} for liveness checks
 *
 * Payloads are stored as individual JSON files under sys_get_temp_dir()/asaas-webhooks/.
 * Each filename encodes a monotonic timestamp + random suffix to preserve order
 * and avoid collisions across concurrent requests.
 *
 * Environment:
 *   ASAAS_WEBHOOK_TOKEN — When set, the server rejects requests whose
 *                         asaas-access-token header does not match (returns 401).
 *                         Leave unset to accept any request (useful for smoke tests).
 */

declare(strict_types=1);

// ──────────────────────────────────────────────────────────────
// Bootstrap
// ──────────────────────────────────────────────────────────────

$storageDir = sys_get_temp_dir() . '/asaas-webhooks';

if (! is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// ──────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────

function respond(int $status, mixed $body = null): never
{
    http_response_code($status);
    header('Content-Type: application/json');

    if ($body !== null) {
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    exit;
}

/** @return array<int, array<string, mixed>> */
function readEvents(string $dir): array
{
    $files = glob($dir . '/*.json');

    if ($files === false || $files === []) {
        return [];
    }

    sort($files);

    $events = [];

    foreach ($files as $file) {
        $content = file_get_contents($file);

        if ($content === false) {
            continue;
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            $events[] = $decoded;
        }
    }

    return $events;
}

function clearEvents(string $dir): void
{
    $files = glob($dir . '/*.json');

    if ($files === false) {
        return;
    }

    foreach ($files as $file) {
        unlink($file);
    }
}

function getRequestHeader(string $name): ?string
{
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

    if (isset($_SERVER[$key]) && is_string($_SERVER[$key])) {
        return $_SERVER[$key];
    }

    return null;
}

// ──────────────────────────────────────────────────────────────
// Routing
// ──────────────────────────────────────────────────────────────

// Health check
if ($path === '/health') {
    respond(200, ['ok' => true]);
}

// Event log
if ($path === '/events') {
    if ($method === 'GET') {
        respond(200, readEvents($storageDir));
    }

    if ($method === 'DELETE') {
        clearEvents($storageDir);
        respond(204);
    }

    respond(405, ['error' => 'Method not allowed']);
}

// Incoming webhook
if ($method === 'POST') {
    // Optional token verification
    $expectedToken = getenv('ASAAS_WEBHOOK_TOKEN');

    if ($expectedToken !== false && $expectedToken !== '') {
        $received = getRequestHeader('asaas-access-token');

        if (! hash_equals($expectedToken, (string) $received)) {
            respond(401, ['error' => 'Invalid asaas-access-token']);
        }
    }

    $body = file_get_contents('php://input');

    if ($body === false || $body === '') {
        respond(400, ['error' => 'Empty request body']);
    }

    /** @var array<string, mixed>|null $decoded */
    $decoded = json_decode($body, true);

    if (! is_array($decoded)) {
        respond(400, ['error' => 'Request body is not valid JSON']);
    }

    // Store with microsecond precision + random suffix for uniqueness
    $filename = sprintf(
        '%s/%s_%s.json',
        $storageDir,
        number_format(microtime(true), 6, '.', ''),
        bin2hex(random_bytes(4)),
    );

    file_put_contents($filename, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    respond(200, ['received' => true]);
}

// Fallback for GET / and anything else (expose health probes, etc.)
respond(200, ['ok' => true]);
