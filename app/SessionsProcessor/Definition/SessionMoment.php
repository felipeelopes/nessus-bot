<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\Definition;

use Application\Adapters\Telegram\Update;
use Application\Types\Process;

abstract class SessionMoment
{
    /**
     * Request user interation.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     */
    abstract public function request(Update $update, Process $process): void;

    /**
     * Save the user input.
     * @param string  $input   User input message.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function save(string $input, Update $update, Process $process): ?string
    {
        return null;
    }

    /**
     * Validate the moment initialization.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return bool
     */
    public function validateInitialization(Update $update, Process $process): bool
    {
        return true;
    }

    /**
     * Validate user input.
     * @param string  $input   User input message.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        return null;
    }
}
