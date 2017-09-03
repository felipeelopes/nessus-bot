<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Predefinition\OptionItem;
use Application\Services\Contracts\ServiceContract;
use Illuminate\Contracts\Session\Session;
use Illuminate\Translation\Translator;

class PredefinitionService implements ServiceContract
{
    /**
     * @var array|null
     */
    private $options;

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
     * @return null|string
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

            if (!$commandDescription) {
                /** @var Translator $trans */
                $trans    = app('translator');
                $transKey = 'Command.commands.' . $optionDescription->command . 'Description';

                if ($trans->has($transKey)) {
                    $commandDescription = trans($transKey);
                }
            }

            if ($optionDescription->command && !ctype_digit($optionDescription->command)) {
                $commandName = trans('Command.commands.' . $optionDescription->command . 'Command', $optionDescription->arguments ?? []);
            }

            $commands .= trans('Predefinition.listOption', [
                'command'     => '/' . $commandName,
                'description' => $commandDescription ? ' - ' . $commandDescription : null,
            ]);
        }

        return $commands;
    }

    /**
     * Returns all predefined options from runtime options or from Session.
     * @return OptionItem[]
     */
    public function getOptions(): array
    {
        /** @var Session $session */
        $session = app(Session::class);
        $options = $this->options ?? $session->get('PredefinitionService@options', []);

        return array_map([ self::class, 'setOptionCommand' ], $options, array_keys($options));
    }

    /**
     * Set all predefined options.
     */
    public function setOptions(?array $options = null, ?bool $updateSession = null): void
    {
        $this->options = $options;

        if ($updateSession !== false) {
            /** @var Session $session */
            $session = app(Session::class);
            $session->put('PredefinitionService@options', $options);
        }
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
}
