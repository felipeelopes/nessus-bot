<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @property-read int                        $message_id         Message id.
 * @property-read User|null                  $from               Message sender.
 * @property-read Carbon                     $date               Message unixtime.
 * @property-read Chat                       $chat               Message chat.
 * @property string|null                     $text               Message text.
 * @property Collection|MessageEntity[]|null $entities           Message entities.
 * @property-read User|null                  $left_chat_member   User left.
 * @property-read User|null                  $new_chat_member    User new.
 */
class Message extends BaseFluent
{
    /**
     * @inheritdoc
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->instantiate('from', User::class);
        $this->instantiate('chat', Chat::class);
        $this->instantiateCollection('entities', MessageEntity::class);
        $this->instantiate('left_chat_member', User::class);
        $this->instantiate('new_chat_member', User::class);

        if ($this->text) {
            $this->text = trim($this->text);
        }

        $this->date = Carbon::createFromTimestamp((int) $this->date);
    }

    /**
     * Treats this message as Private.
     */
    public function forcePrivate()
    {
        $this->chat = new Chat([
            'id'         => $this->from->id,
            'first_name' => $this->from->first_name,
            'last_name'  => $this->from->last_name,
            'username'   => $this->from->username,
            'type'       => Chat::TYPE_PRIVATE,
        ]);
    }

    /**
     * Treats this message as Public.
     */
    public function forcePublic()
    {
        $this->chat = new Chat([
            'id'    => env('NBOT_GROUP_ID'),
            'title' => BotService::getInstance()->getChat()->title,
            'type'  => Chat::TYPE_SUPERGROUP,
        ]);
    }

    /**
     * Returns a command if available.
     * @return MessageEntityBotCommand|null
     */
    public function getCommand(): ?MessageEntityBotCommand
    {
        if (!$this->entities) {
            return null;
        }

        foreach ($this->entities as $entity) {
            $botCommand = $entity->bot_command;

            if ($botCommand) {
                if ($entity->offset === 0) {
                    return $botCommand;
                }

                break;
            }
        }

        return null;
    }

    /**
     * Check if the called command.
     * @param string|null $command Command name.
     * @return bool
     */
    public function isCommand(?string $command = null): bool
    {
        $botCommand = $this->getCommand();

        if ($botCommand === null) {
            return false;
        }

        if ($command === null) {
            return $botCommand->command !== null;
        }

        return $botCommand->command === '/' . strtolower(trans('Command.commands.' . $command . 'Command'));
    }

    /**
     * Identify if message is directly to Bot.
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->chat->type === Chat::TYPE_PRIVATE;
    }

    /**
     * Return the message reference (useful for deleteMessage API).
     * @return Message
     */
    public function onlyReference(): Message
    {
        return new self([
            'message_id' => $this->message_id,
            'chat'       => [ 'id' => $this->chat->id ],
        ]);
    }
}
