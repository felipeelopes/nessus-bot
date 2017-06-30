<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read string $bot      Bot name.
 * @property-read string $command  Bot command.
 * @property-read string $argument Bot command argument.
 */
class MessageEntityBotCommand extends BaseFluent
{
}
