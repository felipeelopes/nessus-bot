<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Models\User;

class CommandService
{
    public const COMMAND_CANCEL             = 'cancel';
    public const COMMAND_COMMANDS           = 'commands';
    public const COMMAND_GRID_SHOW_SHORT    = 'gridShowShort';
    public const COMMAND_LIST_GRIDS         = 'listGrids';
    public const COMMAND_MY_GRIDS           = 'myGrids';
    public const COMMAND_NEWS               = 'news';
    public const COMMAND_NEW_GRID           = 'newGrid';
    public const COMMAND_REGISTER           = 'register';
    public const COMMAND_RULES              = 'rules';
    public const COMMAND_START              = 'start';

    /**
     * Returns the Command Service instance.
     * @return CommandService
     */
    public static function getInstance(): CommandService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Build the command list.
     * @param User|null $user User instance.
     * @return string
     */
    public function buildList(?User $user): string
    {
        // Main commands.
        $commands   = [];
        $commands[] = static::COMMAND_COMMANDS;

        if (env('NBOT_OPTION_BETA_MODULES')) {
            if ($user !== null) {
                $commands[] = static::COMMAND_NEW_GRID;
                $commands[] = static::COMMAND_LIST_GRIDS;
                $commands[] = static::COMMAND_MY_GRIDS;
            }
        }

        $result = $this->buildCommandsList(trans('Command.mainCommands'), $commands);

        // Additional commands.
        $commands = [];

        if ($user === null) {
            $commands[] = static::COMMAND_REGISTER;
        }

        $commands[] = static::COMMAND_RULES;

        $result .= $this->buildCommandsList(trans('Command.additionalCommands'), $commands);

        return $result;
    }

    /**
     * Builds the commands list.
     * @param string $title    Commands list title.
     * @param array  $commands Commands.
     * @return string
     */
    private function buildCommandsList(string $title, array $commands): ?string
    {
        $result = null;

        foreach ($commands as $command) {
            $result .= trans('Command.command', [
                'command'     => '/' . trans('Command.commands.' . $command . 'Command'),
                'description' => trans('Command.commands.' . $command . 'Description'),
            ]);
        }

        return $title . $result;
    }
}
