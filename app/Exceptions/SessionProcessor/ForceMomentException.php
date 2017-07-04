<?php

declare(strict_types = 1);

namespace Application\Exceptions\SessionProcessor;

use RuntimeException;

class ForceMomentException extends RuntimeException
{
    /**
     * Store the new moment.
     * @var string
     */
    private $moment;

    /**
     * ForceMomentException constructor.
     * @param string $moment Force a new moment.
     */
    public function __construct(string $moment)
    {
        $this->moment = $moment;
    }

    /**
     * Returns the new moment.
     * @return string
     */
    public function getMoment(): string
    {
        return $this->moment;
    }
}
