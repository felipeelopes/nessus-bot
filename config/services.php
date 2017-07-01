<?php

declare(strict_types = 1);

return [
    /**
     * Third-party services.
     */
    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],
];
