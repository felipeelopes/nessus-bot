<?php

declare(strict_types = 1);

namespace Application\Controllers;

use Application\Adapters\Telegram\Update;
use Application\Controllers\Contracts\RouterRegisterContract;
use Application\Services\MockupService;
use Application\Services\SessionService;
use Application\Strategies\CancelCommandStrategy;
use Application\Strategies\EdgeCommandStrategy;
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
        $requestData = json_decode($request->getContent(), true);

        if (env('APP_ENV') === 'local') {
            file_put_contents('debug/' . microtime(true) . '.json', json_encode($requestData, JSON_PRETTY_PRINT));
        }

        $this->processUpdate(new Update($requestData));
    }

    /**
     * Process an Update instance.
     * @param Update $update Update instance.
     */
    public function processUpdate(Update $update): void
    {
        $mockupService = MockupService::getInstance();
        $mockupService->singleton(SessionService::class, [ $update ]);

        if (!$update->message &&
            !$update->callback_query) {
            return;
        }

        /** @var CancelCommandStrategy $cancelCommand */
        $cancelCommand = $mockupService->instance(CancelCommandStrategy::class);
        if ($cancelCommand->process($update)) {
            return;
        }

        /** @var UserSubscriptionStrategy $userRegistration */
        $userSubscription = $mockupService->instance(UserSubscriptionStrategy::class);
        if ($userSubscription->process($update)) {
            return;
        }

        if (!$update->message->text) {
            return;
        }

        /** @var UserRegistrationStrategy $userRegistration */
        $userRegistration = $mockupService->instance(UserRegistrationStrategy::class);
        if ($userRegistration->process($update)) {
            return;
        }

        /** @var EdgeCommandStrategy $userRegistration */
        $edgeCommand = $mockupService->instance(EdgeCommandStrategy::class);
        $edgeCommand->process($update);
    }
}
