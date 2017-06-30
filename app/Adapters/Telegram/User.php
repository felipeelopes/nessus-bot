<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read int         $id            User id.
 * @property-read string      $first_name    User first name.
 * @property-read string|null $last_name     User last name.
 * @property-read string|null $username      User username.
 * @property-read string|null $language_code User language code tag (IETF).
 */
class User extends BaseFluent
{
    /**
     * Returns the fullname of User.
     * @return string
     */
    public function getFullname(): string
    {
        return implode(' ', array_filter([ $this->first_name, $this->last_name ]));
    }
}
