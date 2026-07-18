<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'tenant' => [
            'driver' => 'session',
            'provider' => 'tenant_users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'tenant_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\TenantUser::class,
        ],
    ],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
        ],
        'tenant_users' => [
            'provider' => 'tenant_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
        ],
    ],
];
