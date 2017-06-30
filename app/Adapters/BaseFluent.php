<?php

declare(strict_types = 1);

namespace Application\Adapters;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * @property-read BaseFluent $parent Parent instance.
 */
class BaseFluent extends Fluent
{
    /**
     * Process Fluent data if it is declared and instantiate a new class with this.
     * @param string $key   Data key.
     * @param string $class Data class type.
     */
    public function instantiate(string $key, string $class): void
    {
        if ($this->offsetExists($key)) {
            $value           = $this->offsetGet($key);
            $value['parent'] = $this;

            $this->offsetSet($key, new $class($value));
        }
    }

    /**
     * Process Fluent data as array and set on a Collection based on a class.
     * @param string $key   Data key.
     * @param string $class Data class type.
     */
    protected function instantiateCollection(string $key, string $class): void
    {
        if ($this->offsetExists($key)) {
            $collection = new Collection($this->offsetGet($key));

            $this->offsetSet($key, $collection->map(function ($data) use ($class) {
                $data['parent'] = $this;

                return new $class($data);
            }));
        }
    }
}
