<?php

declare(strict_types = 1);

namespace Application\Controllers;

use Application\Adapters\Telegram\Update;
use Application\Controllers\Contracts\RouterRegisterContract;
use Application\Events\GoodMorningExecutor;
use Application\Services\MockupService;
use Application\Services\PredefinitionService;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\Strategies\CancelCommandStrategy;
use Application\Strategies\EdgeCommandStrategy;
use Application\Strategies\GridCreationStrategy;
use Application\Strategies\GridListingStrategy;
use Application\Strategies\GridModificationStrategy;
use Application\Strategies\PredefinitionStrategy;
use Application\Strategies\UserRegistrationStrategy;
use Application\Strategies\UserStatsStrategy;
use Application\Strategies\UserSubscriptionStrategy;
use GuzzleHttp\Exception\RequestException;
use Route;
use Symfony\Component\HttpFoundation\Request;

class BotController extends Controller implements RouterRegisterContract
{
    /**
     * @inheritdoc
     */
    public static function routerRegister(): void
    {
        Route::get('/', 'BotController@hello');
        Route::post('/' . env('NBOT_WEBHOOK_ID'), 'BotController@process')
            ->middleware('web');
    }

    /**
     * Simple hello message.
     * @return string
     */
    public function hello(): string
    {
        return 'Hello. I\'m the NessusBot!';
    }

    /**
     * Process an Updates request from Telegram.
     * @throws \Exception
     */
    public function process(): void
    {
        try {
            $this->processUpdate(BotService::getInstance()->getUpdate());
        }
        catch (RequestException $exception) {
            /** @var Request $request */
            $request = app('request');

            /** @var \Raven_Client $sentry */
            $sentry = app('sentry');
            $sentry->captureException($exception, [
                'extra' => [
                    'Update' => json_decode($request->getContent(), true),
                ],
            ]);
        }
    }

    /**
     * Process an Update instance.
     * @param Update $update Update instance.
     * @throws \Exception
     */
    public function processUpdate(Update $update): void
    {
        $mockupService = MockupService::getInstance();
        $mockupService->singleton(SessionService::class, [ $update ]);
        $mockupService->singleton(PredefinitionService::class);

        if (!$update->message) {
            return;
        }

        /** @var UserService $userService */
        $userService = MockupService::getInstance()->instance(UserService::class);
        $user        = $userService->get($update->message->from->id);

        if ($user && $update->message->from) {
            $user->user_username  = $update->message->from->username;
            $user->user_firstname = $update->message->from->first_name;
            $user->user_lastname  = $update->message->from->last_name;
            $user->user_language  = $update->message->from->language_code;
            $user->touch();
        }

        (new GoodMorningExecutor)->run();

        if ($user && $user->exists && !$user->deleted_at && $user->gamertag) {
            /** @var PredefinitionStrategy $predefinition */
            $predefinition = $mockupService->instance(PredefinitionStrategy::class);
            if ($predefinition->process($user, $update)) {
                return;
            }

            /** @var CancelCommandStrategy $cancelCommand */
            $cancelCommand = $mockupService->instance(CancelCommandStrategy::class);
            if ($cancelCommand->process($user, $update)) {
                return;
            }
        }

        /** @var UserSubscriptionStrategy $userRegistration */
        $userSubscription = $mockupService->instance(UserSubscriptionStrategy::class);
        if ($userSubscription->process($update)) {
            return;
        }

        if ($update->message->text === null) {
            return;
        }

        if ($update->message->text === '') {
            $update->message->text = null;
        }

        /** @var UserRegistrationStrategy $userRegistration */
        $userRegistration = $mockupService->instance(UserRegistrationStrategy::class);
        if ($userRegistration->process($user, $update)) {
            return;
        }

        if ($user && $user->exists && !$user->deleted_at && $user->gamertag) {
            /** @var GridListingStrategy $gridListing */
            $gridListing = $mockupService->instance(GridListingStrategy::class);
            if ($gridListing->process($user, $update)) {
                return;
            }

            /** @var GridModificationStrategy $gridModification */
            $gridModification = $mockupService->instance(GridModificationStrategy::class);
            if ($gridModification->process($user, $update)) {
                return;
            }

            /** @var GridCreationStrategy $gridCreation */
            $gridCreation = $mockupService->instance(GridCreationStrategy::class);
            if ($gridCreation->process($user, $update)) {
                return;
            }

            /** @var UserStatsStrategy $userStats */
            $userStats = $mockupService->instance(UserStatsStrategy::class);
            if ($userStats->process($user, $update)) {
                return;
            }
        }

        /** @var EdgeCommandStrategy $userRegistration */
        $edgeCommand = $mockupService->instance(EdgeCommandStrategy::class);
        $edgeCommand->process($user, $update);
    }
}
