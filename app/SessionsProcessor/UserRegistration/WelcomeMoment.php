<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\UserRegistration;

use Application\Adapters\Live\Gamertag;
use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\RequestException as MomentRequestException;
use Application\Exceptions\Telegram\RequestException;
use Application\Services\Assertions\EventService;
use Application\Services\CommandService;
use Application\Services\GamertagService;
use Application\Services\Live\LiveService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

/**
 * Remove last user message and send the Gamertag request.
 */
class WelcomeMoment extends SessionMoment
{
    public const EVENT_CHECK_GAMERTAG_INVALID   = 'CheckGamertagInvalid';
    public const EVENT_CHECK_GAMERTAG_NOT_FOUND = 'CheckGamertagNotFound';
    public const EVENT_CHECK_GAMERTAG_SUCCESS   = 'CheckGamertagSuccess';
    public const EVENT_DELETE_MESSAGE           = 'DeleteMessage';
    public const EVENT_WELCOME_PRIVATE          = 'WelcomePrivate';
    public const EVENT_WELCOME_PUBLIC           = 'WelcomePublic';

    private const PROCESS_GAMERTAG = 'gamertagInstance';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $groupId = env('NBOT_GROUP_ID');

        assert(EventService::getInstance()->register(self::EVENT_DELETE_MESSAGE));

        $botService = BotService::getInstance();
        $botService->deleteMessage($update->message->chat->id, $update->message->message_id);

        try {
            $botService->sendCancelableMessage(
                $update->message->from->id,
                trans('UserRegistration.welcome', [
                    'groupTitle'    => $botService->getChat($groupId)->title,
                    'whichGamertag' => trans('UserRegistration.whichGamertag'),
                ])
            );

            assert(EventService::getInstance()->register(self::EVENT_WELCOME_PRIVATE));
        }
        catch (RequestException $requestException) {
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserRegistration.toPrivate', [
                    'fullname'    => $update->message->from->getFullname(),
                    'botUsername' => '@' . $botService->getMe()->username,
                ])
            );

            assert(EventService::getInstance()->register(self::EVENT_WELCOME_PUBLIC));

            throw new MomentRequestException;
        }
    }

    /**
     * @inheritdoc
     */
    public function save(string $input, Update $update, Process $process): ?string
    {
        /** @var Gamertag $gamertagInstance */
        $gamertagInstance = $process->offsetGet(self::PROCESS_GAMERTAG);
        $gamertag         = $gamertagInstance->value;

        $botService = BotService::getInstance();
        $botService->sendMessage(
            $update->message->from->id,
            trans('UserRegistration.checkingSuccess', [
                'rules' => trans('UserRules.followIt'),
            ])
        );

        $botService->sendPublicSticker(trans('UserRegistration.welcomeToGroupSticker'));
        $botService->sendPublicMessage(
            trans('UserRegistration.welcomeToGroup', [
                'fullname' => $update->message->from->getFullname(),
                'gamertag' => $gamertag,
            ])
        );

        $user = UserService::getInstance()->register($update->message->from);
        GamertagService::getInstance()->register($user, $gamertagInstance);

        assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_SUCCESS));

        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateInitialization(Update $update, Process $process): bool
    {
        // Ignore registration check if message was sent directly to Bot.
        // Except if is the "/start" command.
        return !$update->message->isPrivate() ||
               $update->message->isCommand(CommandService::COMMAND_START) ||
               $update->message->isCommand(CommandService::COMMAND_REGISTER);
    }

    /**
     * @inheritdoc
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        $botService      = BotService::getInstance();
        $gamertagService = GamertagService::getInstance();

        if (!$gamertagService->isValid($input)) {
            $botService->sendCancelableMessage($update->message->from->id, trans('UserRegistration.checkingInvalid', [
                'whichGamertag' => trans('UserRegistration.whichGamertag'),
            ]));

            assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_INVALID));

            return self::class;
        }

        $botService->sendMessage($update->message->from->id, trans('UserRegistration.checkingGamertag'));

        $gamertagInstance = LiveService::getInstance()->getGamertag($input);

        if (!$gamertagInstance) {
            $botService->sendCancelableMessage($update->message->from->id, trans('UserRegistration.checkingFail', [
                'gamertag'      => $input,
                'whichGamertag' => trans('UserRegistration.whichGamertag'),
            ]));

            assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_NOT_FOUND));

            return self::class;
        }

        $process->offsetSet(self::PROCESS_GAMERTAG, $gamertagInstance);

        return null;
    }

}
