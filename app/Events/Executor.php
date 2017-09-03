<?php

declare(strict_types = 1);

namespace Application\Events;

abstract class Executor
{
    /**
     * Event statements to be run.
     * It reports a failure if returns false.
     */
    abstract public function run(): ?bool;
}
