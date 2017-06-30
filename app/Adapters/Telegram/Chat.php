<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read int         $id                              Chat id.
 * @property-read string      $type                            Chat type (TYPE consts).
 * @property-read string|null $title                           Chat title.
 * @property-read string|null $username                        Chat username.
 * @property-read string|null $first_name                      Chat user first name.
 * @property-read string|null $last_name                       Chat user last name.
 * @property-read bool|null   $all_members_are_administrators  Is all member administrators?
 */
class Chat extends BaseFluent
{
    public const TYPE_CHANNEL    = 'channel';
    public const TYPE_GROUP      = 'group';
    public const TYPE_PRIVATE    = 'private';
    public const TYPE_SUPERGROUP = 'supergroup';
}
