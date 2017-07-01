<?php

declare(strict_types = 1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait Bootstrap
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
