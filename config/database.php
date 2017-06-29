<?php

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
            'strict'    => true,
            'engine'    => null,
        ],
    ],

    /**
     * Migration repository table.
     */
    'migrations'  => 'migrations',
];
