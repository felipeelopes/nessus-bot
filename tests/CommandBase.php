<?php

declare(strict_types = 1);

namespace Tests;

use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\MessageEntity;
use Application\Adapters\Telegram\Update;
use Application\Controllers\BotController;
use Application\Controllers\Kernel;
use Application\Models\User;
use Application\Models\UserGamertag;
use Application\Services\Assertions\EventService;
use Application\Services\MockupService;
use Application\Services\Requester\Live\RequesterService as LiveRequesterService;
use Application\Services\Requester\Telegram\RequesterService as TelegramRequesterService;
use Artisan;
use Cache;
use Carbon\Carbon;
use DB;
use PHPUnit\Framework\TestCase;
use Session;
use Tests\Mockups\Requester\Live\RequesterServiceMockup as LiveRequesterServiceMockup;
use Tests\Mockups\Requester\Telegram\RequesterServiceMockup as TelegramRequesterServiceMockup;

/**
 * @mixin TestCase
 */
abstract class CommandBase extends TestCase
{
    /**
     * Simulates a private message from User to Bot.
     * @param string   $text     Message text.
     * @param callable $callable Event assertion processor.
     */
    protected function assertBotMessage(string $text, callable $callable): void
    {
        $entities = null;

        if (strpos($text, '/') === 0) {
            $entities = [
                [
                    'type'   => MessageEntity::TYPE_BOT_COMMAND,
                    'offset' => 0,
                    'length' => strpos($text, ' ') ?: strlen($text),
                ],
            ];
        }

        $this->assertUpdate(new Update([
            'message' => array_filter([
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
                'entities'   => $entities,
            ]),
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

    /**
     * Create a fake user and set on session.
     */
    protected function createFakeUser(): void
    {
        $user                 = new User;
        $user->user_number    = 20000;
        $user->user_firstname = 'John';
        $user->user_lastname  = 'Doe';
        $user->user_language  = 'pt-BR';
        $user->save();

        $userGamertag                 = new UserGamertag;
        $userGamertag->user_id        = $user->id;
        $userGamertag->gamertag_value = 'JohnDoe';
        $userGamertag->save();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $table) {
            $tableName = array_first($table);

            if (strpos($tableName, DB::getTablePrefix()) === 0) {
                DB::statement("DROP TABLE {$tableName}");
            }
        }

        Artisan::call('migrate');

        MockupService::getInstance()->singleton(EventService::class);

        $mockupService = MockupService::getInstance();
        $mockupService->mockup(TelegramRequesterService::class, TelegramRequesterServiceMockup::class);
        $mockupService->mockup(LiveRequesterService::class, LiveRequesterServiceMockup::class);

        EventService::getInstance()->clear();

        Cache::flush();
        Session::flush();
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        /** @var Kernel $appKernel */
        $appKernel = $app->make(\Illuminate\Foundation\Console\Kernel::class);
        $appKernel->bootstrap();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $mockupService = MockupService::getInstance();
        $mockupService->reset();
    }
}
