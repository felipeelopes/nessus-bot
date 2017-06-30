<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @property-read int                             $message_id         Message id.
 * @property-read User|null                       $from               Message sender.
 * @property-read Carbon                          $date               Message unixtime.
 * @property-read Chat                            $chat               Message chat.
 * @property-read string|null                     $text               Message text.
 * @property-read Collection|MessageEntity[]|null $entities           Message entities.
 * @property-read User|null                       $left_chat_member   User left.
 * @property-read User|null                       $new_chat_member    User new.
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

        $this->date = Carbon::createFromTimestamp((int) $this->date);
    }

    /**
     * Returns a command if available.
     * @return null|string
     */
    public function getCommand(): ?string
    {
        $text = $this->text;

        if (strpos($text, '/') === 0) {
            $posAt = strpos($text, '@');

            if ($posAt !== false) {
                return strtolower(substr($text, 0, $posAt));
            }

            $posSpace = strpos($text, ' ');

            if ($posSpace !== false) {
                return strtolower(substr($text, 0, $posSpace));
            }

            return strtolower($text);
        }

        return null;
    }
}
