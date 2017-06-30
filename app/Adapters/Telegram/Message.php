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

        $this->date = Carbon::createFromTimestamp((int) $this->date);
    }
}
