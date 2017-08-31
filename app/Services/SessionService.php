<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\ForceMomentException;
use Application\Exceptions\SessionProcessor\RequestException;
use Application\Exceptions\SessionProcessor\SkipParentMomentException;
use Application\Services\Contracts\ServiceContract;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;
use Illuminate\Contracts\Session\Session;

class SessionService implements ServiceContract
{
    private const SESSION_MOMENT_CURRENT     = __CLASS__ . '@momentCurrent';
    private const SESSION_MOMENT_INITIALIZER = __CLASS__ . '@momentInitializer';
    private const SESSION_PROCESS            = __CLASS__ . '@process';

    /**
     * @var string
     */
    private $currentMoment;

    /**
     * Initial moment class.
     * @var string
     */
    private $initialMoment;

    /**
     * @var Process
     */
    private $process;

    /**
     * SessionService constructor.
     * @param Update $update Update instance.
     */
    public function __construct(Update $update)
    {
        $messageContainer = $update->message ?? $update->callback_query;

        if (!$messageContainer) {
            return;
        }

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
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Clear current moment.
     */
    public function clearMoment(): void
    {
        /** @var Session $session */
        $session = app(Session::class);

        $session->put(self::SESSION_MOMENT_INITIALIZER);
        $session->put(self::SESSION_MOMENT_CURRENT);

        /** @var Process $processInstance */
        $processInstance = $session->get(self::SESSION_PROCESS);

        if ($processInstance !== null) {
            $processInstance->clear();
        }
    }

    /**
     * Run the current Session Moment.
     * @param Update $update Update instance.
     * @return bool|null
     */
    public function run(Update $update): ?bool
    {
        /** @var Session $session */
        $session = app(Session::class);

        $momentInitializer = $session->get(self::SESSION_MOMENT_INITIALIZER);

        // Ignore if is not this Session Moment.
        if ($momentInitializer !== null) {
            if ($momentInitializer !== $this->initialMoment ||
                !$update->message->isPrivate()) {
                return null;
            }
        }

        /** @var SessionMoment $momentInstance */
        $momentCurrent   = $this->currentMoment ?? $session->get(self::SESSION_MOMENT_CURRENT);
        $processInstance = $this->process ?? $session->get(self::SESSION_PROCESS) ?? new Process;
        $momentNull      = $momentCurrent === null;

        if ($this->currentMoment === null) {
            if ($momentNull || $update->message->isPrivate()) {
                if ($momentNull) {
                    $momentCurrent = $this->initialMoment;

                    if (!$this->process) {
                        $processInstance->clear();
                    }
                }
                else {
                    $momentInstance = new $momentCurrent;
                    $forcedMoment   = false;

                    try {
                        $momentReturned = $momentInstance->validateInput($update->message->text, $update, $processInstance);
                    }
                    catch (ForceMomentException $momentException) {
                        $momentReturned = $momentException->getMoment();
                        $forcedMoment   = true;
                    }

                    if ($momentReturned !== null) {
                        $session->put(self::SESSION_MOMENT_CURRENT, $momentReturned);

                        if ($momentReturned === $momentCurrent && $forcedMoment !== true) {
                            return true;
                        }
                    }
                    else {
                        $momentReturned = $momentInstance->save($update->message->text, $update, $processInstance);

                        if ($momentReturned === null) {
                            $this->clearMoment();

                            return true;
                        }

                        $session->put(self::SESSION_MOMENT_CURRENT, $momentReturned);
                        $session->put(self::SESSION_PROCESS, $processInstance);
                    }

                    $momentCurrent = $momentReturned;
                }
            }
        }

        $momentInstance = new $momentCurrent;

        try {
            if ($momentInstance->validateInitialization($update, $processInstance) === false) {
                return null;
            }
        }
        catch (SkipParentMomentException $ignore) {
            $session->put(self::SESSION_PROCESS, $processInstance);

            return true;
        }
        catch (ForceMomentException $forceMomentException) {
            $session->put(self::SESSION_MOMENT_CURRENT, $forceMomentException->getMoment());

            return true;
        }

        try {
            $momentInstance->request($update, $processInstance);

            if ($momentInitializer === null) {
                $session->put(self::SESSION_MOMENT_INITIALIZER, $this->initialMoment);
            }

            if ($momentCurrent === $this->initialMoment) {
                $session->put(self::SESSION_MOMENT_CURRENT, $this->initialMoment);
            }
        }
        catch (ForceMomentException $forceMomentException) {
            $session->put(self::SESSION_MOMENT_CURRENT, $forceMomentException->getMoment());
        }
        catch (RequestException $requestException) {
            $this->clearMoment();

            return null;
        }

        $session->put(self::SESSION_PROCESS, $processInstance);

        return true;
    }

    /**
     * Set the initial moment.
     * @param string $initialMomentClass Session Moment initial class.
     */
    public function setInitialMoment(string $initialMomentClass): void
    {
        assert(is_subclass_of($initialMomentClass, SessionMoment::class));

        $this->initialMoment = $initialMomentClass;
    }

    /**
     * Set the current moment.
     * @param string $currentMomentClass Current moment class.
     */
    public function setMoment(string $currentMomentClass): void
    {
        assert(is_subclass_of($currentMomentClass, SessionMoment::class));

        $this->currentMoment = $currentMomentClass;

        /** @var Session $session */
        $session = app(Session::class);
        $session->put(self::SESSION_MOMENT_CURRENT, $currentMomentClass);
    }

    /**
     * Reuse a previous instantiated Process.
     * @param Process $process Process instance.
     */
    public function withProcess(Process $process): void
    {
        $this->process = $process;
    }
}
