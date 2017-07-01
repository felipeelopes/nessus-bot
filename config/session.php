<?php

declare(strict_types = 1);

return [
    /**
     * Default session driver.
     */
    'driver'          => env('SESSION_DRIVER', 'file'),

    /**
     * Session lifetime in minutes.
     */
    'lifetime'        => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,

    /**
     * Session encryption.
     */
    'encrypt'         => false,

    /**
     * Session file location.
     */
    'files'           => storage_path('framework/sessions'),

    /**
     * Session cache store.
     */
    'store'           => null,

    /**
     * Session sweeping lottery.
     */
    'lottery'         => [ 1, 65535 ],

    /**
     * Session cookie name.
     */
    'cookie'          => 'session',

    /**
     * Session cookie path.
     */
    'path'            => '/',

    /**
     * Session cookie domain.
     */
    'domain'          => env('SESSION_DOMAIN'),

    /**
     * HTTPS-only cookies.
     */
    'secure'          => env('SESSION_SECURE_COOKIE', false),

    /**
     * HTTP access-only.
     */
    'http_only'       => true,
];
