<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

/**
 * Register the autoloader.
 */
require __DIR__ . '/../bootstrap/autoload.php';

/**
 * Turn on the lights.
 */
$app = require __DIR__ . '/../bootstrap/app.php';

/**
 * Run the application.
 * @var Kernel $kernel
 */
$kernel   = $app->make(Kernel::class);
$request  = Request::capture();
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
