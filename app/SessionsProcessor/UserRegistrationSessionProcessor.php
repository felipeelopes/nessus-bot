<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\Telegram\RequestException;
use Application\Services\Assertions\EventService;
use Application\Services\CommandService;
use Application\Services\GamertagService;
use Application\Services\Live\LiveService;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\Definition\SessionProcessor;
use Session;

class UserRegistrationSessionProcessor extends SessionProcessor
{
    public const EVENT_CHECK_GAMERTAG_INVALID   = 'CheckGamertagInvalid';
    public const EVENT_CHECK_GAMERTAG_NOT_FOUND = 'CheckGamertagNotFound';
    public const EVENT_CHECK_GAMERTAG_SUCCESS   = 'CheckGamertagSuccess';
    public const EVENT_DELETE_MESSAGE           = 'DeleteMessage';
    public const EVENT_PRIVATE_WELCOME          = 'PrivateWelcome';
    public const EVENT_PUBLIC_MESSAGE           = 'PublicWelcome';

    public const  MOMENT_ACCEPTED = 'accepted';
    private const MOMENT_CHECK    = 'check';
    private const MOMENT_WELCOME  = 'welcome';

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->register(self::MOMENT_WELCOME, [ $this, 'momentWelcome' ]);
        $this->register(self::MOMENT_CHECK, [ $this, 'momentCheck' ]);
    }

    /**
     * Check the user Gamertag on Xbox Live service.
     * @param Update $update Update instance.
     * @return string|null
     */
    public function momentCheck(Update $update): ?string
    {
        // Check only if you are talking with the bot.
        if (!$update->message->isPrivate()) {
            return $this->momentWelcome($update);
        }

        $botService      = BotService::getInstance();
        $gamertagService = GamertagService::getInstance();
        $gamertag        = trim($update->message->text);

        if (!$gamertagService->isValid($gamertag)) {
            $botService->sendCancelableMessage($update->message->from->id, trans('UserRegistration.checkingInvalid', [
                'whichGamertag' => trans('UserRegistration.whichGamertag'),
            ]));

            assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_INVALID));

            return self::MOMENT_CHECK;
        }

        /** @var LiveService $liveService */
        $liveService      = MockupService::getInstance()->instance(LiveService::class);
        $gamertagInstance = $liveService->getGamertag($gamertag);

        if (!$gamertagInstance) {
            $botService->sendCancelableMessage($update->message->from->id, trans('UserRegistration.checkingFail', [
                'gamertag'      => $gamertag,
                'whichGamertag' => trans('UserRegistration.whichGamertag'),
            ]));

            assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_NOT_FOUND));

            return self::MOMENT_CHECK;
        }

        $gamertag = $gamertagInstance->value;

        $botService->sendMessage(
            $update->message->from->id,
            trans('UserRegistration.checkingSuccess', [
                'rules' => trans('UserRules.followIt'),
            ])
        );

        $botService->sendPublicSticker('CAADAQADAgADwvySEW6F5o6Z1x05Ag');
        $botService->sendPublicMessage(
            trans('UserRegistration.welcomeToGroup', [
                'fullname' => $update->message->from->getFullname(),
                'gamertag' => $gamertag,
            ])
        );

        $user = UserService::getInstance()->register($update->message->from);
        GamertagService::getInstance()->register($user, $gamertagInstance);

        assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_SUCCESS));

        return self::MOMENT_ACCEPTED;
    }

    /**
     * Remove last user message and send the Gamertag request.
     * @param Update $update Update instance.
     * @return string|null
     */
    public function momentWelcome(Update $update): ?string
    {
        // Ignore registration check if message was sent directly to Bot.
        // Except if is the "/start" command.
        if ($update->message->isPrivate() &&
            !$update->message->isCommand(CommandService::COMMAND_START) &&
            !$update->message->isCommand(CommandService::COMMAND_REGISTER)) {
            return null;
        }

        $groupId = env('NBOT_GROUP_ID');

        if (!$update->message->isPrivate()) {
            Session::put($groupId, $update->message->chat->id);
        }

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

            assert(EventService::getInstance()->register(self::EVENT_PRIVATE_WELCOME));
        }
        catch (RequestException $requestException) {
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserRegistration.toPrivate', [
                    'fullname'    => $update->message->from->getFullname(),
                    'botUsername' => '@' . $botService->getMe()->username,
                ])
            );

            assert(EventService::getInstance()->register(self::EVENT_PUBLIC_MESSAGE));

            return self::MOMENT_ACCEPTED;
        }

        return self::MOMENT_CHECK;
    }
}
