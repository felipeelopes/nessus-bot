<?php

declare(strict_types = 1);

namespace Application\Adapters;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use JsonSerializable;

/**
 * @property-read BaseFluent $parent Parent instance.
 */
class BaseFluent extends Fluent
{
    /**
     * BaseFluent constructor.
     * @param array|null $attributes
     */
    public function __construct($attributes = null)
    {
        if ($attributes !== null) {
            parent::__construct($attributes);
        }
    }

    /**
     * Strip recursivity from array.
     * @param array $attributes Attributes to be analyzed.
     * @param array $recursions Recursions to be avoided.
     */
    private static function stripRecursivity(array $attributes, array &$recursions)
    {
        foreach ($attributes as &$attribute) {
            if (is_array($attribute)) {
                $attribute = self::stripRecursivity($attribute, $recursions);
            }

            if (!is_object($attribute)) {
                continue;
            }

            if (in_array($attribute, $recursions, true)) {
                $attribute = '{&...}';
                continue;
            }

            $recursions[] = $attribute;

            if ($attribute instanceof JsonSerializable) {
                $attribute = self::stripRecursivity($attribute->jsonSerialize(), $recursions);
                continue;
            }

            $attribute = (array) $attribute;
        }

        return $attributes;
    }

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
     * Convert the Fluent instance to an array.
     * It should remove any recursion.
     */
    public function toArrayUnrecursive(): array
    {
        $recursions = [ $this ];

        return self::stripRecursivity($this->attributes, $recursions);
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
