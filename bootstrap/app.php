<?php

use Application\Controllers\Kernel;
use Application\Exceptions\Handler as ApplicationExceptionHandler;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Create the application.
 */
$app = new Application(realpath(__DIR__ . '/..'));

/**
 * Bind important interfaces.
 */
$app->singleton(HttpKernelContract::class, Kernel::class);
$app->singleton(ConsoleKernelContract::class, ConsoleKernel::class);
$app->singleton(ExceptionHandler::class, ApplicationExceptionHandler::class);

/**
 * Return the application.
 */
return $app;
