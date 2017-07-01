<?php

declare(strict_types = 1);

namespace Application\Controllers;

use Application\Adapters\Telegram\Update;
use Application\Controllers\Contracts\RouterRegisterContract;
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
     * Process Updates from Telegram.
     * @param Request $request
     */
    public function process(Request $request): void
    {
        $requestData = json_decode($request->getContent(), true);

        if (env('APP_ENV') === 'local') {
            file_put_contents('debug/' . microtime(true) . '.json', json_encode($requestData, JSON_PRETTY_PRINT));
        }

        $requestUpdate  = new Update($requestData);
        $sessionService = new SessionService($requestUpdate);

        app()->singleton(SessionService::class, function () use ($sessionService) {
            return $sessionService;
        });

        if (!$requestUpdate->message &&
            !$requestUpdate->callback_query) {
            return;
        }

        /** @var CancelCommandStrategy $cancelCommand */
        $cancelCommand = app(CancelCommandStrategy::class);
        if ($cancelCommand->process($requestUpdate)) {
            return;
        }

        /** @var UserSubscriptionStrategy $userRegistration */
        $userSubscription = app(UserSubscriptionStrategy::class);
        if ($userSubscription->process($requestUpdate)) {
            return;
        }

        if (!$requestUpdate->message->text) {
            return;
        }

        /** @var UserRegistrationStrategy $userRegistration */
        $userRegistration = app(UserRegistrationStrategy::class);
        if ($userRegistration->process($requestUpdate)) {
            return;
        }

        /** @var EdgeCommandStrategy $userRegistration */
        $edgeCommand = app(EdgeCommandStrategy::class);
        $edgeCommand->process($requestUpdate);
    }
}
