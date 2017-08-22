<?php
declare(strict_types = 1);

namespace Application\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;

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
            /** @var Request $request */
            $request = app('request');

            /** @var \Raven_Client $sentry */
            $sentry = app('sentry');
            $sentry->captureException($eexception, [
                'extra' => [
                    'Update' => json_decode($request->getContent(), true),
                ],
            ]);
        }

        parent::report($eexception);
    }
}
