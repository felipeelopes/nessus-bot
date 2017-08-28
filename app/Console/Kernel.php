<?php

declare(strict_types = 1);

namespace Application\Console;

use Application\Console\Commands\EventsProcessor;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * @var string[]
     */
    protected $commands = [
        EventsProcessor::class,
    ];
}
