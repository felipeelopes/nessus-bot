<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\Definition;

use Application\Adapters\Telegram\Update;
use Application\Services\SessionService;
use Illuminate\Support\Collection;

abstract class SessionProcessor
{
    /**
     * Registered moments.
     * @var Collection|callable[]
     */
    private $registeredMoments;

    /**
     * SessionProcessor constructor.
     */
    public function __construct()
    {
        $this->registeredMoments = new Collection;
    }

    /**
     * Initialize a Session Processor instance.
     */
    abstract public function initialize(): void;

    /**
     * Run the Session Processor identifying the right moment.
     * @param Update $update
     * @return string|null
     */
    public function run(Update $update): ?string
    {
        $momentBase = static::class . '@';
        $moment     = SessionService::getInstance()->getMoment();

        if ($moment !== null &&
            strpos($moment, $momentBase) === 0) {
            if (!$update->message->isPrivate()) {
                return null;
            }

            $momentKey = substr($moment, strlen($momentBase));

            if ($this->registeredMoments->has($momentKey)) {
                return $this->runMoment($this->registeredMoments->get($momentKey), $update, $momentBase);
            }
        }

        return $this->runMoment($this->registeredMoments->first(), $update, $momentBase);
    }

    /**
     * Register a new moment for this Session Processor.
     * @param string   $moment   Moment name.
     * @param callable $callable Moment callable.
     */
    protected function register(string $moment, callable $callable): void
    {
        $this->registeredMoments[$moment] = $callable;
    }

    /**
     * Run a moment and return a based moment name.
     * @param callable $callable   Moment callable.
     * @param Update   $update     Update instance.
     * @param string   $momentBase Moment class base.
     * @return string|null
     */
    private function runMoment(callable $callable, Update $update, string $momentBase): ?string
    {
        $sessionService = SessionService::getInstance();
        $process        = $sessionService->getProcess();
        $nextMoment     = $callable($update, $process);

        $sessionService->setProcess($process);

        return $nextMoment !== null
            ? $momentBase . $nextMoment
            : null;
    }
}
