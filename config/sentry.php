<?php

declare(strict_types = 1);

return [
    'dsn'                      => env('SENTRY_DSN'),
    'breadcrumbs.sql_bindings' => true,
    'user_context'             => true,
];
