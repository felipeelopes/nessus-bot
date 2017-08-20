<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read int                $update_id        Update id.
 * @property Message|null            $message          Message object.
 * @property-read CallbackQuery|null $callback_query   Query object.
 */
class Update extends BaseFluent
{
    /**
     * @inheritdoc
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->instantiate('message', Message::class);
        $this->instantiate('callback_query', CallbackQuery::class);
    }
}
