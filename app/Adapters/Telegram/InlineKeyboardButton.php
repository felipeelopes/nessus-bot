<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property string      $text          Button text.
 * @property string|null $callback_data Callback data.
 */
class InlineKeyboardButton extends BaseFluent
{
}
