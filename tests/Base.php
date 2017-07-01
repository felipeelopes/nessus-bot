<?php

declare(strict_types = 1);

namespace Tests;

use PHPUnit\Framework\TestSuite;

abstract class Base extends TestSuite
{
    use Bootstrap;
}
