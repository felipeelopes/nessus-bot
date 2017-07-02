<?php

declare(strict_types = 1);

namespace Tests;

use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\Update;
use Application\Controllers\BotController;
use Application\Controllers\Kernel;
use Application\Services\Assertions\EventService;
use Application\Services\MockupService;
use Application\Services\Requester\Live\RequesterService as LiveRequesterService;
use Application\Services\Requester\Telegram\RequesterService as TelegramRequesterService;
use Artisan;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Tests\Mockups\Requester\Live\RequesterServiceMockup as LiveRequesterServiceMockup;
use Tests\Mockups\Requester\Telegram\RequesterServiceMockup as TelegramRequesterServiceMockup;

/**
 * @mixin TestCase
 */
abstract class CommandBase extends TestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        /** @var Kernel $appKernel */
        $appKernel = $app->make(\Illuminate\Foundation\Console\Kernel::class);
        $appKernel->bootstrap();

        Artisan::call('migrate');

        MockupService::getInstance()->singleton(EventService::class);

        $mockupService = MockupService::getInstance();
        $mockupService->mockup(TelegramRequesterService::class, TelegramRequesterServiceMockup::class);
        $mockupService->mockup(LiveRequesterService::class, LiveRequesterServiceMockup::class);
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        Artisan::call('migrate:rollback');

        $mockupService = MockupService::getInstance();
        $mockupService->mockup(TelegramRequesterService::class);
        $mockupService->mockup(LiveRequesterService::class);
    }

    /**
     * Simulates a private message from User to Bot.
     * @param string   $text     Message text.
     * @param callable $callable Event assertion processor.
     */
    protected function assertBotMessage(string $text, callable $callable): void
    {
        $this->assertUpdate(new Update([
            'message' => [
                'message_id' => 10000,
                'from'       => [
                    'id'            => 20000,
                    'first_name'    => 'John',
                    'last_name'     => 'Doe',
                    'language_code' => 'pt-BR',
                ],
                'chat'       => [
                    'id'         => 20000,
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'type'       => Chat::TYPE_PRIVATE,
                ],
                'date'       => (new Carbon)->getTimestamp(),
                'text'       => $text,
            ],
        ]), $callable);
    }

    /**
     * Simulates a public message from User to group.
     * @param string   $text     Message text.
     * @param callable $callable Event assertion processor.
     */
    protected function assertPublicMessage(string $text, callable $callable): void
    {
        $this->assertUpdate(new Update([
            'message' => [
                'message_id' => 10000,
                'from'       => [
                    'id'            => 20000,
                    'first_name'    => 'John',
                    'last_name'     => 'Doe',
                    'language_code' => 'pt-BR',
                ],
                'chat'       => [
                    'id'    => env('NBOT_GROUP_ID'),
                    'title' => 'Public Group',
                    'type'  => 'supergroup',
                ],
                'date'       => (new Carbon)->getTimestamp(),
                'text'       => $text,
            ],
        ]), $callable);
    }

    /**
     * Simulares a Telegram Update.
     * @param Update   $update   Update instance.
     * @param callable $callable Event assertion processor.
     */
    protected function assertUpdate(Update $update, callable $callable): void
    {
        /** @var EventService $eventService */
        $eventService = MockupService::getInstance()->instance(EventService::class);
        $eventService->clear();

        /** @var BotController $botController */
        $botController = MockupService::getInstance()->instance(BotController::class);
        $botController->processUpdate($update);

        $callable($eventService);
    }
}
