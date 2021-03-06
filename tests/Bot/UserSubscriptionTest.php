<?php

declare(strict_types = 1);

namespace Tests\Bot;

use Application\Adapters\Telegram\RequestResponse;
use Application\Exceptions\Telegram\RequestException;
use Application\Services\Assertions\EventService;
use Application\Services\MockupService;
use Application\SessionsProcessor\UserRegistration\WelcomeMoment;
use PHPUnit\Framework\TestCase;
use Tests\CommandBase;
use Tests\Mockups\Requester\Live\RequesterServiceMockup as LiveRequesterServiceMockup;
use Tests\Mockups\Requester\Telegram\RequesterServiceMockup as TelegramRequesterServiceMockup;

/**
 * @mixin TestCase
 */
class UserSubscriptionTest extends CommandBase
{
    public function testUserSubscription(): void
    {
        $mockupService = MockupService::getInstance();

        $mockupServiceThrowFirstOnly = true;
        $mockupService->registerProvider(
            TelegramRequesterServiceMockup::class,
            function (string $method, string $action, array $params) use (&$mockupServiceThrowFirstOnly) {
                if ($mockupServiceThrowFirstOnly === true) {
                    $mockupServiceThrowFirstOnly = false;

                    throw new RequestException(new RequestResponse([ 'description' => '403 Forbidden' ]));
                }
            }
        );

        $this->assertPublicMessage('First Message with Bot still not Started', function (EventService $eventService) {
            $this->assertTrue($eventService->has(WelcomeMoment::EVENT_DELETE_MESSAGE));
            $this->assertTrue($eventService->has(WelcomeMoment::EVENT_WELCOME_PUBLIC));
        });

        $mockupService->registerProvider(TelegramRequesterServiceMockup::class, function () {
            return null;
        });

        $this->assertPublicMessage('First Message with Bot Started', function (EventService $eventService) {
            $this->assertTrue($eventService->has(WelcomeMoment::EVENT_DELETE_MESSAGE));
            $this->assertTrue($eventService->has(WelcomeMoment::EVENT_WELCOME_PRIVATE));
        });

        $mockupService->registerProvider(LiveRequesterServiceMockup::class, function () {
            return null;
        });

        $this->assertBotMessage('VeryLongGamertagIsInvalid', function (EventService $eventService) {
            $this->assertTrue($eventService->has(WelcomeMoment::EVENT_CHECK_GAMERTAG_INVALID));
        });

        // It will be mocked to be not found initially.
        if (env('NBOT_OPTION_XBOX_CHECK')) {
            $this->assertBotMessage('ValidGamertag', function (EventService $eventService) {
                $this->assertTrue($eventService->has(WelcomeMoment::EVENT_CHECK_GAMERTAG_NOT_FOUND));
            });

            $mockupService->registerProvider(LiveRequesterServiceMockup::class, function () {
                return json_encode([ 'id' => 123, 'Gamertag' => 'ValidGamertag' ]);
            });
        }

        // Now we mock to be found.
        $this->assertBotMessage('ValidGamertag', function (EventService $eventService) {
            $this->assertTrue($eventService->has(WelcomeMoment::EVENT_CHECK_GAMERTAG_SUCCESS));
        });

        $mockupService->registerProvider(TelegramRequesterServiceMockup::class, function (string $method, string $action, array $params) {
            if ($action === 'sendMessage' && array_get($params, 'query.reply_markup')) {
                throw new RequestException(new RequestResponse([ 'description' => '403 Forbidden' ]));
            }
        });

        $this->assertPublicMessage('First Public Message', function (EventService $eventService) {
            $this->assertFalse($eventService->has(WelcomeMoment::EVENT_DELETE_MESSAGE));
        });
    }
}
