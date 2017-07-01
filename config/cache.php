<?php

return [
    /**
     * Default cache store.
     */
    'default' => env('CACHE_DRIVER', 'file'),

    /**
     * Cache stores.
     */
    'stores'  => [
        'array' => [
            'driver' => 'array',
        ],
        'file'  => [
            'driver' => 'file',
            'path'   => storage_path('framework/cache/data'),
        ],
        'redis' => [
            'driver'     => 'redis',
            'connection' => 'default',
        ],
    ],

    /**
     * Cache key prefix.
     */
    'prefix'  => 'laravel',
];
