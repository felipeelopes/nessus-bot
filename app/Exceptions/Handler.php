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
        if ($this->shouldReport($eexception) && env('SENTRY_DSN')) {
            /** @var \Raven_Client $sentry */
            $sentry = app('sentry');
            $sentry->captureException($eexception, [
                'extra' => [ '$_POST' => $_POST ],
            ]);
        }

        parent::report($eexception);
    }
}
