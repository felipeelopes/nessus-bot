<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor;

use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\Update;
use Application\Exceptions\Telegram\RequestException;
use Application\Services\GamertagService;
use Application\Services\Live\LiveService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\Definition\SessionProcessor;
use Session;

class UserRegistrationSessionProcessor extends SessionProcessor
{
    private const GROUP_ID_KEY = __CLASS__ . '@' . __FUNCTION__ . ':groupId';

    private const MOMENT_CHECK      = 'check';
    public const  MOMENT_REGISTERED = 'registered';
    private const MOMENT_WELCOME    = 'welcome';

    /**
     * Returns the group title if this information is available.
     * @param $groupTitle
     * @return string|null
     */
    private static function getGroupTitle($groupTitle): ?string
    {
        return $groupTitle !== null
            ? trans('UserRegistration.welcomeGroupTitle', [ 'group' => $groupTitle ])
            : null;
    }

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
        if ($update->message->chat->type !== Chat::TYPE_PRIVATE) {
            return $this->momentWelcome($update);
        }

        $botService      = BotService::getInstance();
        $gamertagService = GamertagService::getInstance();
        $gamertag        = trim($update->message->text);

        if (!$gamertagService->isValid($gamertag)) {
            $botService->sendMessageCancelable($update->message->from->id, trans('UserRegistration.checkingInvalid', [
                'whichGamertag' => trans('UserRegistration.whichGamertag'),
            ]));

            return self::MOMENT_CHECK;
        }

        /** @var LiveService $liveService */
        $liveService    = app(LiveService::class);
        $gamertagExists = $liveService->gamertagExists($gamertag);

        if (!$gamertagExists) {
            $botService->sendMessageCancelable($update->message->from->id, trans('UserRegistration.checkingFail', [
                'gamertag'      => $gamertag,
                'whichGamertag' => trans('UserRegistration.whichGamertag'),
            ]));

            return self::MOMENT_CHECK;
        }

        $botService->sendMessage(
            $update->message->from->id,
            trans('UserRegistration.checkingSuccess', [
                'gamertag' => $gamertag,
            ])
        );

        $groupId = Session::get(self::GROUP_ID_KEY);

        if ($groupId) {
            $botService->sendSticker($groupId, 'CAADAQADAgADwvySEW6F5o6Z1x05Ag');
            $botService->sendMessage(
                $groupId,
                trans('UserRegistration.welcomeToGroup', [
                    'fullname' => $update->message->from->getFullname(),
                    'gamertag' => $gamertag,
                ])
            );
        }

        $user = UserService::getInstance()->register($update->message->from);
        GamertagService::getInstance()->register($user, $gamertag);

        return self::MOMENT_REGISTERED;
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
        if ($update->message->chat->type === Chat::TYPE_PRIVATE &&
            $update->message->text !== '/start') {
            return null;
        }

        Session::put(self::GROUP_ID_KEY, $update->message->chat->id);

        $botService = BotService::getInstance();
        $botService->deleteMessage($update->message->chat->id, $update->message->message_id);

        try {
            $botService->sendMessageCancelable(
                $update->message->from->id,
                trans('UserRegistration.welcome', [
                    'groupTitle'    => self::getGroupTitle($update->message->chat->title),
                    'whichGamertag' => trans('UserRegistration.whichGamertag'),
                ])
            );
        }
        catch (RequestException $requestException) {
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserRegistration.toPrivate', [
                    'fullname' => $update->message->from->getFullname(),
                    'botname'  => $botService->getMe()->username,
                ])
            );

            return null;
        }

        return self::MOMENT_CHECK;
    }
}
