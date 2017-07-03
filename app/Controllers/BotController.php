<?php

declare(strict_types = 1);

namespace Application\Controllers;

use Application\Adapters\Telegram\Update;
use Application\Controllers\Contracts\RouterRegisterContract;
use Application\Services\MockupService;
use Application\Services\PredefinitionService;
use Application\Services\SessionService;
use Application\Services\UserService;
use Application\Strategies\CancelCommandStrategy;
use Application\Strategies\EdgeCommandStrategy;
use Application\Strategies\GridSubscriptionStrategy;
use Application\Strategies\PredefinitionStrategy;
use Application\Strategies\UserRegistrationStrategy;
use Application\Strategies\UserSubscriptionStrategy;
use Illuminate\Http\Request;
use Route;

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
     * @param Request $request Telegram request.
     */
    public function process(Request $request): void
    {
        $this->processUpdate(new Update(json_decode($request->getContent(), true)));
    }

    /**
     * Process an Update instance.
     * @param Update $update Update instance.
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

        /** @var UserSubscriptionStrategy $userRegistration */
        $userSubscription = $mockupService->instance(UserSubscriptionStrategy::class);
        if ($userSubscription->process($update)) {
            return;
        }

        if ($update->message->text === null) {
            return;
        }

        /** @var UserRegistrationStrategy $userRegistration */
        $userRegistration = $mockupService->instance(UserRegistrationStrategy::class);
        if ($userRegistration->process($user, $update)) {
            return;
        }

        /** @var GridSubscriptionStrategy $gridSubscription */
        $gridSubscription = $mockupService->instance(GridSubscriptionStrategy::class);
        if ($gridSubscription->process($user, $update)) {
            return;
        }

        /** @var EdgeCommandStrategy $userRegistration */
        $edgeCommand = $mockupService->instance(EdgeCommandStrategy::class);
        $edgeCommand->process($user, $update);
    }
}
