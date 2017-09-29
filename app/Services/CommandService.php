<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Telegram\Update;

class CommandService
{
    public const COMMAND_ADMINS          = 'admins';
    public const COMMAND_BAN             = 'ban';
    public const COMMAND_CANCEL          = 'cancel';
    public const COMMAND_COMMANDS        = 'commands';
    public const COMMAND_CONFIRM         = 'confirm';
    public const COMMAND_GRID_SHOW_SHORT = 'gridShowShort';
    public const COMMAND_GT              = 'gt';
    public const COMMAND_LINKS           = 'links';
    public const COMMAND_LIST_GRIDS      = 'listGrids';
    public const COMMAND_MY_GRIDS        = 'myGrids';
    public const COMMAND_NEWS            = 'news';
    public const COMMAND_NEW_GRID        = 'newGrid';
    public const COMMAND_RANKING         = 'ranking';
    public const COMMAND_RECALC_STATS    = 'recalcStats';
    public const COMMAND_REFRESH         = 'refresh';
    public const COMMAND_REGISTER        = 'register';
    public const COMMAND_RULES           = 'rules';
    public const COMMAND_SELF_STATS      = 'selfStats';
    public const COMMAND_START           = 'start';
    public const COMMAND_STATS           = 'stats';

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
     * @param Update $update Update instace.
     * @return string
     */
    public function buildList(Update $update): string
    {
        $user         = $update->message->from;
        $userRegister = $user->getUserRegister();

        // Main commands.
        $commands   = [];
        $commands[] = static::COMMAND_COMMANDS;

        if ($userRegister !== null) {
            $commands[] = static::COMMAND_NEW_GRID;
            $commands[] = static::COMMAND_LIST_GRIDS;
            $commands[] = static::COMMAND_MY_GRIDS;
            $commands[] = static::COMMAND_RANKING;
            $commands[] = static::COMMAND_STATS;
            $commands[] = static::COMMAND_SELF_STATS;
            $commands[] = static::COMMAND_GT;
        }

        $result = $this->buildCommandsList(trans('Command.mainCommands'), $commands);

        // Additional commands.
        $commands = [];

        if ($userRegister === null) {
            $commands[] = static::COMMAND_REGISTER;
        }

        $commands[] = static::COMMAND_LINKS;
        $commands[] = static::COMMAND_ADMINS;
        $commands[] = static::COMMAND_RULES;

        $result .= $this->buildCommandsList(trans('Command.additionalCommands'), $commands);

        // Admin commands.
        if ($user->isAdminstrator() && $update->message->isPrivate()) {
            $commands = [];

            $commands[] = static::COMMAND_BAN;
            $commands[] = static::COMMAND_REFRESH;

            $result .= $this->buildCommandsList(trans('Command.adminCommands'), $commands);
        }

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
