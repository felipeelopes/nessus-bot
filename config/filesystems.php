<?php

return [
    /**
     * Default filesystem disk.
     */
    'default' => 'local',

    /**
     * Default cloud filesystem disk.
     */
    'cloud'   => 's3',

    /**
     * Filesystem disks.
     */
    'disks'   => [
        'local'  => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],
        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],
        's3'     => [
            'driver' => 's3',
            'key'    => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
    ],
];
