<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asaas API Key
    |--------------------------------------------------------------------------
    |
    | Your default Asaas API key. In multi-tenant applications, you can
    | override this at runtime using Asaas::withApiKey($tenantKey).
    |
    */
    'api_key' => env('ASAAS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Asaas Environment
    |--------------------------------------------------------------------------
    |
    | "production" or "sandbox". Controls which base URL is used for all
    | requests. Accepts any value of the AsaasEnvironment enum.
    |
    */
    'environment' => env('ASAAS_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeouts
    |--------------------------------------------------------------------------
    |
    | timeout         — seconds to wait for the full response (default: 30)
    | connect_timeout — seconds to wait for the connection to be established (default: 10)
    |
    */
    'timeout'         => env('ASAAS_TIMEOUT', 30),
    'connect_timeout' => env('ASAAS_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Webhook Auth Token
    |--------------------------------------------------------------------------
    |
    | The authToken you set when creating an Asaas webhook configuration.
    | Asaas sends this value in the asaas-access-token header on every
    | incoming webhook request. The VerifyAsaasWebhook middleware uses it to
    | authenticate incoming requests.
    |
    */
    'webhook_token' => env('ASAAS_WEBHOOK_TOKEN', ''),

];
