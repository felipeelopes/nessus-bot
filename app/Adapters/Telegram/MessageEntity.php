<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;
use Application\Services\MockupService;
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

        if ($this->isType(static::TYPE_BOT_COMMAND)) {
            $commandMessage = $this->getContent();

            [ $botCommand, $botName ] = array_pad(explode('@', $commandMessage), 2, null);

            if ($botName === null) {
                /** @var BotService $botService */
                $botService = MockupService::getInstance()->instance(BotService::class);
                $botName    = $botService->getMe()->username;
            }

            $botArguments = [];

            if (preg_match('/(?<command>\/[a-z]+)(?<arguments>(?:\S+)?)/i', $botCommand, $botArgumentMatch)) {
                $botCommand = $botArgumentMatch['command'];

                if (preg_match_all('/(?<argument>\d+(?=$|_|\B)|[a-z]+)/i', $botArgumentMatch['arguments'], $botArgumentsMatch, PREG_SET_ORDER)) {
                    $botArguments = array_pluck($botArgumentsMatch, 'argument');
                }
            }

            /** @var Message $message */
            $message = $this->parent;

            $this->bot_command = new MessageEntityBotCommand([
                'bot'       => '@' . $botName,
                'command'   => strtolower($botCommand),
                'arguments' => $botArguments,
                'text'      => $message->text,
                'entities'  => array_filter($message->entities, function ($entity) {
                    return $entity['type'] !== self::TYPE_BOT_COMMAND;
                }),
            ]);
        }
    }

    /**
     * Returns the entity content.
     * @return string
     */
    public function getContent(): string
    {
        /** @var Message $message */
        $message = $this->parent;

        return substr($message->text, $this->offset, $this->length);
    }

    /**
     * Check if type match.
     * @param string $type Type.
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }
}
