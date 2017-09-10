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
use Application\Strategies\EdgeCommandStrategy;
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
        assert(EventService::getInstance()->register(self::EVENT_DELETE_MESSAGE));

        $botService = BotService::getInstance();
        $botService->deleteMessage($update->message->onlyReference());

        try {
            $botService->createMessage($update->message)
                ->setPrivate()
                ->setCancelable()
                ->appendMessage(trans('UserRegistration.welcome', [
                    'groupTitle'    => $botService->getChat()->title,
                    'whichGamertag' => trans('UserRegistration.whichGamertag'),
                ]))
                ->allowExceptions()
                ->publish();

            assert(EventService::getInstance()->register(self::EVENT_WELCOME_PRIVATE));
        }
        catch (RequestException $requestException) {
            $botService->createMessage($update->message)
                ->forcePublic()
                ->appendMessage(trans('UserRegistration.toPrivate', [
                    'mention' => $update->message->from->getMention(),
                ]))
                ->unduplicate(__CLASS__ . '@' . __FUNCTION__ . '@' . $update->message->from->id)
                ->addLinkButton(
                    trans('UserRegistration.toPrivateButton'),
                    trans('UserRegistration.toPrivateLink', [
                        'botname' => $botService->getMe()->username,
                    ])
                )
                ->publish();

            assert(EventService::getInstance()->register(self::EVENT_WELCOME_PUBLIC));

            throw new MomentRequestException;
        }
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Gamertag $gamertagInstance */
        $gamertagInstance = $process->offsetGet(self::PROCESS_GAMERTAG);
        $gamertag         = $gamertagInstance->value;

        $botService = BotService::getInstance();
        $botService->createMessage($update->message)
            ->setPrivate()
            ->appendMessage(trans('UserRegistration.checkingSuccess', [
                'rules'  => trans('UserRules.followIt'),
                'admins' => trans('UserRules.adminHeader', [
                    'admins' => implode(EdgeCommandStrategy::getAdministratorsList($botService)),
                ]),
            ]))
            ->publish();

        $botService->sendPublicSticker(trans('UserRegistration.welcomeToGroupSticker'));
        $botService->createMessage($update->message)
            ->forcePublic()
            ->appendMessage(trans('UserRegistration.welcomeToGroup', [
                'mention'  => $update->message->from->getMention(),
                'gamertag' => $gamertag,
            ]))
            ->publish();

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
        if ($update->message->from->is_bot) {
            return false;
        }

        // Ignore registration check if message was sent directly to Bot.
        // Except if is the "/start" command.
        return !$update->message->isPrivate() ||
               $update->message->isCommand(CommandService::COMMAND_START) ||
               $update->message->isCommand(CommandService::COMMAND_REGISTER);
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        $botService      = BotService::getInstance();
        $gamertagService = GamertagService::getInstance();

        if (!$gamertagService->isValid($input)) {
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('UserRegistration.checkingInvalid', [
                    'whichGamertag' => trans('UserRegistration.whichGamertag'),
                ]))
                ->publish();

            assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_INVALID));

            return self::class;
        }

        if (env('NBOT_OPTION_XBOX_CHECK')) {
            $botService->createMessage($update->message)
                ->setPrivate()
                ->appendMessage(trans('UserRegistration.checkingGamertag'))
                ->publish();

            $gamertagInstance = LiveService::getInstance()->getGamertag($input);

            if (!$gamertagInstance) {
                $botService->createMessage($update->message)
                    ->setCancelable()
                    ->appendMessage(trans('UserRegistration.checkingFail', [
                        'gamertag'      => $input,
                        'whichGamertag' => trans('UserRegistration.whichGamertag'),
                    ]))
                    ->publish();

                assert(EventService::getInstance()->register(self::EVENT_CHECK_GAMERTAG_NOT_FOUND));

                return self::class;
            }
        }
        else {
            $gamertagInstance = new Gamertag([ 'value' => $input ]);
        }

        $process->offsetSet(self::PROCESS_GAMERTAG, $gamertagInstance);

        return null;
    }

}
