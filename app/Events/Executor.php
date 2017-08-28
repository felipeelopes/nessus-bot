<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;

abstract class Executor
{
    /**
     * Event statements to be run.
     * It reports a failure if returns false.
     * @param Model|null $model Model instance to be argumented.
     * @return bool|null
     */
    abstract public function run(?Model $model): ?bool;
}
