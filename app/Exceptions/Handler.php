<?php
declare(strict_types = 1);

namespace Application\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * Class Handler
 * @package Application\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * @inheritdoc
     */
    public function report(Exception $eexception): void
    {
        if ($this->shouldReport($eexception)) {
            app('sentry')->captureException($eexception);
        }

        parent::report($eexception);
    }
}
