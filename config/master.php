<?php

return [
    'base_domain' => env('BASE_DOMAIN', 'dinofy.app'),
    'dinofy_image' => env('DINOFY_IMAGE', 'dinofy_app:latest'),
    'tenant_data_path' => env('TENANT_DATA_PATH', '/srv/tenants'),
    'grace_period_days' => (int) env('BILLING_GRACE_DAYS', 3),

    'cajupay' => [
        'base_url' => rtrim(env('CAJUPAY_API_BASE_URL', 'https://api.cajupay.com.br'), '/'),
        'api_key' => env('CAJUPAY_API_KEY'),
        'api_secret' => env('CAJUPAY_API_SECRET'),
        'webhook_secret' => env('CAJUPAY_WEBHOOK_SECRET'),
    ],

    'woovi' => [
        'base_url' => rtrim(env('WOOVI_API_BASE_URL', 'https://api.openpix.com.br'), '/'),
        'app_id' => env('WOOVI_APP_ID'),
        'webhook_secret' => env('WOOVI_WEBHOOK_SECRET'),
    ],
];
