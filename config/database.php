<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'manager_library'),
            'username' => env('DB_USERNAME', 'manager_library'),
            'password' => env('DB_PASSWORD', 'postgres_secret_123'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'library,public',
            'schema' => 'library',
            'sslmode' => 'prefer',
        ],
    ],

    'migrations' => 'migrations',
];
