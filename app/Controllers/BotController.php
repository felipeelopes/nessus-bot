<?php

declare(strict_types = 1);

namespace Application\Controllers;

use Application\Adapters\Telegram\Update;
use Application\Controllers\Contracts\RouterRegisterContract;
use Application\Services\SessionService;
use Application\Strategies\UserRegistrationStrategy;
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
        Route::post('/' . env('NBOT_WEBHOOK_ID'), 'BotController@process');
    }

    /**
     * Simple hello message.
     * @return string
     */
    public function hello(): string
    {
        return 'Hello. I\'m NessusBot!';
    }

    /**
     * Process Updates from Telegram.
     * @param Request $request
     */
    public function process(Request $request): void
    {
        $requestData    = json_decode($request->getContent(), true);
        $requestUpdate  = new Update($requestData);
        $sessionService = new SessionService($requestUpdate);

        app()->singleton(SessionService::class, function () use ($sessionService) {
            return $sessionService;
        });

        if (!$requestUpdate->message) {
            return;
        }

        /** @var UserRegistrationStrategy $userRegistrationStrategy */
        $userRegistrationStrategy = app(UserRegistrationStrategy::class);
        $userRegistrationStrategy->process($requestUpdate);
    }
}
