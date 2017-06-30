<?php

declare(strict_types = 1);

namespace Application\Controllers\Contracts;

interface RouterRegisterContract
{
    /**
     * Router register.
     */
    public static function routerRegister(): void;
}
