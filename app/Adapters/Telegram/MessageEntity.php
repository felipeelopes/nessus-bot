<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;
use Application\Services\Telegram\BotService;

/**
 * @property-read string                       $type        Message entity type (TYPE consts).
 * @property-read int                          $offset      Message entity offset.
 * @property-read int                          $length      Message entity length.
 * @property-read string|null                  $url         Message entity referenced URL (only for TYPE_TEXT_LINK).
 * @property-read User|null                    $user        Message entity referenced user (only for TYPE_TEXT_MENTION).
 * @property-read MessageEntityBotCommand|null $bot_command Message entity bot command.
 */
class MessageEntity extends BaseFluent
{
    public const TYPE_BOLD         = 'bold';
    public const TYPE_BOT_COMMAND  = 'bot_command';
    public const TYPE_CODE         = 'code';
    public const TYPE_EMAIL        = 'email';
    public const TYPE_HASHTAG      = 'hashtag';
    public const TYPE_ITALIC       = 'italic';
    public const TYPE_MENTION      = 'mention';
    public const TYPE_PRE          = 'pre';
    public const TYPE_TEXT_LINK    = 'text_link';
    public const TYPE_TEXT_MENTION = 'text_mention';
    public const TYPE_URL          = 'url';

    /**
     * @inheritdoc
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->instantiate('user', User::class);

        if ($this->type === static::TYPE_BOT_COMMAND) {
            /** @var Message $parentMessage */
            $parentMessage  = $this->parent;
            $commandMessage = substr($parentMessage->text, $this->offset, $this->length);

            [ $botCommand, $botName ] = array_pad(explode('@', $commandMessage), 2, null);

            if ($botName === null) {
                /** @var BotService $botService */
                $botService = app(BotService::class);
                $botName    = $botService->getMe()->username;
            }

            $this->bot_command = new MessageEntityBotCommand([
                'bot'      => '@' . $botName,
                'command'  => $botCommand,
                'argument' => substr($parentMessage->text, $this->length + 1),
            ]);
        }
    }
}
