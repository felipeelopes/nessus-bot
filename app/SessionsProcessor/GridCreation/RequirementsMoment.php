<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Services\Assertions\EventService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class RequirementsMoment extends SessionMoment
{
    public const EVENT_LONG_RESPONSE = 'longResponse';
    public const EVENT_REQUEST       = 'response';
    public const EVENT_SAVE          = 'save';

    private const MAX_REQUIREMENTS = 400;

    public const  PROCESS_REQUIREMENTS = 'requirements';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardRequirements', [ 'max' => self::MAX_REQUIREMENTS ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardRequirementsOptions'))
        );

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function save(string $input, Update $update, Process $process): ?string
    {
        $process->put(self::PROCESS_REQUIREMENTS, $input);

        assert(EventService::getInstance()->register(self::EVENT_SAVE));

        return TimingMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if (strlen($message) > self::MAX_REQUIREMENTS) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorRequirementsTooLong', [ 'max' => self::MAX_REQUIREMENTS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardRequirementsOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_LONG_RESPONSE));

            return self::class;
        }

        return null;
    }
}
