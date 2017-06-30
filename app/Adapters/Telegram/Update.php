<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read int          $update_id Update id.
 * @property-read Message|null $message   Message object.
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
    }
}
