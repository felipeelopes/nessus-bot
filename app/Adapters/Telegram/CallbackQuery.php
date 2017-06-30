<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read string       $id            Callback Query id.
 * @property-read User         $from          Callback Query sender.
 * @property-read Message|null $message       Callback Query message.
 * @property-read string|null  $chat_instance Callback Query chat id.
 * @property-read string|null  $data          Callback Query data.
 *
 */
class CallbackQuery extends BaseFluent
{
    /**
     * @inheritdoc
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->instantiate('from', User::class);
        $this->instantiate('message', Message::class);
    }
}
