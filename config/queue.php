<?php

return [
    /**
     * Default queue driver.
     */
    'default'     => env('QUEUE_DRIVER', 'sync'),

    /**
     * Queue connections.
     */
    'connections' => [
        'sync'     => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver'      => 'database',
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 90,
        ],
    ],

    /**
     * Failed queued jobs.
     */
    'failed'      => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table'    => 'jobs_errors',
    ],
];
