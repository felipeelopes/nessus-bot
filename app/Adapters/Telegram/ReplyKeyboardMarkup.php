<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property KeyboardButton[][] $keyboard          Reply keyboard buttons.
 * @property bool|null          $resize_keyboard   Reply keyboard should be resized?
 * @property bool|null          $one_time_keyboard Reply keyboard should be one time usage?
 */
class ReplyKeyboardMarkup extends BaseFluent
{
}
