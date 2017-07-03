<?php

declare(strict_types = 1);

namespace Application\Types;

use Illuminate\Support\Collection;

class Process extends Collection
{
    /**
     * Remove all items from this Collection.
     */
    public function clear(): Process
    {
        $this->items = [];

        return $this;
    }
}
