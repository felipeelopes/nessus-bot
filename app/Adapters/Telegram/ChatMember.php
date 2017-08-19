<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read User   $user   Member user data.
 * @property-read string $status Member status.
 */
class ChatMember extends BaseFluent
{
    public const STATUS_ADMINISTRATOR = 'administrator';
    public const STATUS_CREATOR       = 'creator';
    public const STATUS_KICKED        = 'kicked';
    public const STATUS_LEFT          = 'left';
    public const STATUS_MEMBER        = 'member';
    public const STATUS_RESTRICTED    = 'restricted';

    /**
     * @inheritdoc
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->instantiate('user', User::class);
    }
}
