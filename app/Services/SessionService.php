<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Telegram\Update;
use Application\Services\Contracts\ServiceContract;
use Application\SessionsProcessor\Definition\SessionProcessor;
use Illuminate\Contracts\Session\Session;

class SessionService implements ServiceContract
{
    /**
     * SessionService constructor.
     * @param Update $update Update instance.
     */
    public function __construct(Update $update)
    {
        $messageContainer = $update->message ?? $update->callback_query;

        /** @var Session $session */
        $session = app(Session::class);
        $session->setId(sha1((string) $messageContainer->from->id));
        $session->start();
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): SessionService
    {
        return app(static::class);
    }

    /**
     * Get current session moment.
     * @return string|null
     */
    public function getMoment(): ?string
    {
        /** @var Session $session */
        $session = app(Session::class);

        return $session->get('SessionService@moment');
    }

    /**
     * Initialize a new SessionProcess class.
     * @param string $class  Session Process class.
     * @param Update $update Update instance.
     */
    public function initializeProcessor(string $class, Update $update): void
    {
        assert(is_subclass_of($class, SessionProcessor::class));

        /** @var SessionProcessor $classInstance */
        $classInstance = new $class;
        $classInstance->initialize();
        $nextMoment = $classInstance->run($update);

        SessionService::getInstance()->setMoment($nextMoment);
    }

    /**
     * Run a callable if is the right moment.
     * @param string   $moment   Moment name.
     * @param callable $callable Callable to be called if is the right moment.
     * @return mixed
     */
    public function run(string $moment, callable $callable)
    {
        if ($this->getMoment() === $moment) {
            return $callable();
        }

        return null;
    }

    /**
     * Set current session moment name.
     * A empty name will clear the moment.
     * @param string|null $name Session moment name.
     */
    public function setMoment(?string $name): void
    {
        /** @var Session $session */
        $session = app(Session::class);

        $session->put('SessionService@moment', $name);
    }
}
