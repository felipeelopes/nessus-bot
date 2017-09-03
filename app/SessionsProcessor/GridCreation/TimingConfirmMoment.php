<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\ForceMomentException;
use Application\Services\Assertions\EventService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class TimingConfirmMoment extends SessionMoment
{
    public const EVENT_CONFIRM = 'confirm';
    public const EVENT_REQUEST = 'request';

    /**
     * @var bool|null
     */
    private static $skipRequest;

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        if (self::$skipRequest !== true) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridCreation.creationWizardTimingConfirm', [
                    'timing' => $process->get(TimingMoment::PROCESS_TIMING_TEXT),
                ]))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardTimingConfirmOptions')))
                ->publish();
        }

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        if (strcasecmp($input, trans('Command.commands.confirmCommand')) !== 0) {
            if (!TimingMoment::processTiming($input, $update, $process)) {
                self::$skipRequest = true;
            }

            throw new ForceMomentException(self::class);
        }

        assert(EventService::getInstance()->register(self::EVENT_CONFIRM));

        return DurationMoment::class;
    }
}
