<?php

return [
    /**
     * Mail driver.
     */
    'driver'     => env('MAIL_DRIVER', 'smtp'),

    /**
     * SMTP host address.
     */
    'host'       => env('MAIL_HOST', 'smtp.mailgun.org'),

    /**
     * SMTP host port.
     */
    'port'       => env('MAIL_PORT', 587),

    /**
     * Global "from" address.
     */
    'from'       => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name'    => env('MAIL_FROM_NAME', 'Example'),
    ],

    /**
     * E-mail encryption protocol.
     */
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),

    /**
     * SMTP server username.
     */
    'username'   => env('MAIL_USERNAME'),
    'password'   => env('MAIL_PASSWORD'),

    /**
     * Sendmail system path.
     */
    'sendmail'   => '/usr/sbin/sendmail -bs',

    /**
     * Markdown mail settings.
     */
    'markdown'   => [
        'theme' => 'default',
        'paths' => [ resource_path('views/vendor/mail') ],
    ],
];
