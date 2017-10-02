<?php

declare(strict_types = 1);

return [
    /**
     * Default database connection name.
     */
    'default'     => env('DB_CONNECTION', 'mysql'),

    /**
     * Database connections.
     */
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ],
        'mysql'  => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => env('DB_PREFIX'),
            'strict'    => false,
            'engine'    => env('DB_ENGINE'),
        ],
    ],

    /**
     * Redis connection.
     */
    'redis'       => [
        'client'  => 'predis',
        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 1,
        ],
    ],

    /**
     * Migration repository table.
     */
    'migrations'  => 'migrations',
];
