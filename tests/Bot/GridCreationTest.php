<?php

declare(strict_types = 1);

namespace Tests\Bot;

use Application\Services\Assertions\EventService;
use Application\Services\CommandService;
use Application\SessionsProcessor\GridCreation\ConfirmMoment;
use Application\SessionsProcessor\GridCreation\PlayersMoment;
use Application\SessionsProcessor\GridCreation\RequirementsMoment;
use Application\SessionsProcessor\GridCreation\SubtitleMoment;
use Application\SessionsProcessor\GridCreation\TimingConfirmMoment;
use Application\SessionsProcessor\GridCreation\TimingMoment;
use Application\SessionsProcessor\GridCreation\TitleMoment;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Tests\CommandBase;

/**
 * @mixin TestCase
 */
class GridCreationTest extends CommandBase
{
    public function testGridCreation(): void
    {
        $this->createFakeUser();

        // Initialize: Title.
        $this->assertBotMessage('/' . trans('Command.commands.' . CommandService::COMMAND_NEW_GRID . 'Command'),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TitleMoment::EVENT_REQUEST));
            });

        $this->assertBotMessage('LongResponse:' . str_random(81),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TitleMoment::EVENT_LONG_RESPONSE));
            });

        $this->assertBotMessage('ValidTitle',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TitleMoment::EVENT_SAVE));
                $this->assertTrue($eventService->has(SubtitleMoment::EVENT_REQUEST));
            });

        // Subtitle.
        $this->assertBotMessage('LongResponse:' . str_random(21),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(SubtitleMoment::EVENT_LONG_RESPONSE));
            });

        $this->assertBotMessage('ValidSubtitle',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(SubtitleMoment::EVENT_SAVE));
                $this->assertTrue($eventService->has(RequirementsMoment::EVENT_REQUEST));
            });

        // Requirements.
        $this->assertBotMessage('LongResponse:' . str_random(401),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(RequirementsMoment::EVENT_LONG_RESPONSE));
            });

        $this->assertBotMessage('ValidRequirements',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(RequirementsMoment::EVENT_SAVE));
                $this->assertTrue($eventService->has(TimingMoment::EVENT_REQUEST));
            });

        // Timing.
        $this->assertBotMessage('InvalidFormat',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TimingMoment::EVENT_INVALID_FORMAT));
            });

        $this->assertBotMessage('10 20 30',
            function (EventService $eventService) {
                // Invalid format too.
                $this->assertTrue($eventService->has(TimingMoment::EVENT_INVALID_FORMAT));
            });

        $this->assertBotMessage('24 01',
            function (EventService $eventService) {
                // Invalid timing.
                $this->assertTrue($eventService->has(TimingMoment::EVENT_INVALID_TIMING));
            });

        $this->assertBotMessage('23 60',
            function (EventService $eventService) {
                // Invalid timing.
                $this->assertTrue($eventService->has(TimingMoment::EVENT_INVALID_TIMING));
            });

        $this->assertBotMessage(Carbon::now()->addMinute(5)->format('H:i'),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TimingMoment::EVENT_INVALID_TOO_CLOSEST));
            });

        $this->assertBotMessage(Carbon::now()->addMinute(20)->format('H:i'),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TimingMoment::EVENT_TIMING_TODAY));
            });

        $this->assertBotMessage(Carbon::now()->subMinute(20)->format('H:i'),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TimingMoment::EVENT_TIMING_TOMORROW));
                $this->assertTrue($eventService->has(TimingConfirmMoment::EVENT_REQUEST));
            });

        // Timing confirm.
        $this->assertBotMessage(trans('GridCreation.creationWizardTimingConfirmYes'),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(TimingConfirmMoment::EVENT_CONFIRM));
                $this->assertTrue($eventService->has(PlayersMoment::EVENT_REQUEST));
            });

        // Players.
        $this->assertBotMessage('abc',
            function (EventService $eventService) {
                // Invalid input.
                $this->assertTrue($eventService->has(PlayersMoment::EVENT_INVALID_COUNT));
            });

        $this->assertBotMessage('1',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(PlayersMoment::EVENT_INVALID_FEW_PLAYERS));
            });

        $this->assertBotMessage('1000',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(PlayersMoment::EVENT_INVALID_MUCH_PLAYERS));
            });

        $this->assertBotMessage('6',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(PlayersMoment::EVENT_SAVE));
                $this->assertTrue($eventService->has(ConfirmMoment::EVENT_REQUEST));
            });

        // Confirm.
        $this->assertBotMessage('InvalidConfirmationResponse',
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(ConfirmMoment::EVENT_INVALID_CONFIRMATION));
            });

        $this->assertBotMessage(trans('GridCreation.creationWizardConfirmCreationYes'),
            function (EventService $eventService) {
                $this->assertTrue($eventService->has(ConfirmMoment::EVENT_SAVE));
            });
    }
}
