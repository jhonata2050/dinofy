<?php

return [
    'default' => env('LOG_CHANNEL', 'stderr'),
    'channels' => [
        'stderr' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => \Monolog\Handler\StreamHandler::class,
            'with' => ['stream' => 'php://stderr'],
        ],
        'stack' => [
            'driver' => 'stack',
            'channels' => ['stderr'],
        ],
    ],
];
