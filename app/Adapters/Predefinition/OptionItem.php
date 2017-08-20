<?php

declare(strict_types = 1);

namespace Application\Adapters\Predefinition;

use Application\Adapters\BaseFluent;

/**
 * @property string|null $command     Option command.
 * @property string[]    $arguments   Option arguments.
 * @property string      $value       Option value.
 * @property string|null $description Option description.
 */
class OptionItem extends BaseFluent
{
    /**
     * Generate an Option Item based on an existing command.
     * @param $commandName
     * @return OptionItem
     */
    public static function fromCommand($commandName): OptionItem
    {
        return new self([ 'command' => $commandName ]);
    }

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
