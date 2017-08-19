<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property int         $chat_id                  Chat id.
 * @property string      $text                     Message text.
 * @property string|null $parse_mode               Message parse mode.
 * @property bool|null   $disable_web_page_preview Disable webpage preview on Message.
 * @property int|null    $reply_to_message_id      Message to reply.
 * @property array       $reply_markup             Reply markup.
 */
class SendMessage extends BaseFluent
{
    public const PARSE_MODE_HTML     = 'HTML';
    public const PARSE_MODE_MARKDOWN = 'Markdown';
}
