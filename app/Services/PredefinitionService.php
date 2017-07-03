<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Predefinition\OptionItem;
use Application\Services\Contracts\ServiceContract;
use Illuminate\Contracts\Session\Session;

class PredefinitionService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): PredefinitionService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Update the option with the command name.
     * @param OptionItem $option       Option instance.
     * @param int        $optionsIndex Option index.
     * @return OptionItem
     */
    private static function setOptionCommand(OptionItem $option, $optionsIndex): OptionItem
    {
        if (!$option->command) {
            $option->command = (string) ($optionsIndex + 1);
        }

        return $option;
    }

    /**
     * Build the predefined options list to be printed.
     * @return string|null
     */
    public function buildOptions(): ?string
    {
        $options = $this->getOptions();

        if (!$options) {
            return null;
        }

        $commands = null;

        /** @var OptionItem $optionDescription */
        foreach ($options as $optionCommandIndex => $optionDescription) {
            $commandName        = $optionCommandIndex + 1;
            $commandDescription = $optionDescription->getDescription();

            if ($optionDescription->command && !ctype_digit($optionDescription->command)) {
                $commandName = trans('Command.commands.' . $optionDescription->command . 'Command');
            }

            $commands .= trans('Predefinition.listOption', [
                'command'     => '/' . $commandName,
                'description' => $commandDescription ? ' - ' . $commandDescription : null,
            ]);
        }

        return trans('Predefinition.listOptions', [
            'options' => $commands,
        ]);
    }

    /**
     * Returns all predefined options on Session.
     * @return OptionItem[]
     */
    public function getOptions(): array
    {
        /** @var Session $session */
        $session = app(Session::class);
        $options = $session->get('PredefinitionService@options', []);

        return array_map([ self::class, 'setOptionCommand' ], $options, array_keys($options));
    }

    /**
     * Returns an Option Item list from raw options.
     * @param array[]|mixed $rawOptions Raw options list.
     * @return OptionItem[]
     */
    public function optionsFrom($rawOptions): array
    {
        $result = [];

        foreach ($rawOptions as $option) {
            $result[] = new OptionItem($option);
        }

        return $result;
    }

    /**
     * Set all predefined options.
     * @param OptionItem[]|null $options List of predefined options.
     */
    public function setOptions(?array $options = null): void
    {
        /** @var Session $session */
        $session = app(Session::class);
        $session->put('PredefinitionService@options', $options);
    }
}
