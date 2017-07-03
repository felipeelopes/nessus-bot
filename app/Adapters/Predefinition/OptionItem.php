<?php

declare(strict_types = 1);

namespace Application\Adapters\Predefinition;

use Application\Adapters\BaseFluent;

/**
 * @property string|null $command     Option command.
 * @property string      $value       Option value.
 * @property string|null $description Option description.
 */
class OptionItem extends BaseFluent
{
    /**
     * Returns the option description.
     * @return string|null
     */
    public function getDescription(): ?string
    {
        if ($this->description) {
            return $this->description;
        }

        return $this->value;
    }
}
