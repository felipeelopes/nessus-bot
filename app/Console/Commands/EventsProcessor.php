<?php

declare(strict_types = 1);

namespace Application\Console\Commands;

use Application\Events\CheckAccountExecutor;
use Application\Events\CountdownExecutor;
use Application\Events\Executor;
use Application\Events\GridFinisherExecutor;
use Application\Events\GridNotifierExecutor;
use Application\Events\TipsExecutor;
use Application\Models\Event;
use Application\Models\Model;
use Application\Services\SettingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class EventsProcessor extends Command
{
    const SETTING_RUNNING = 'running';

    /**
     * @var string
     */
    protected $description = 'Process all system events';

    /**
     * @var string
     */
    protected $signature = 'nessus:events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settingRunning = SettingService::fromReference($this, self::SETTING_RUNNING);

        if ($settingRunning === true) {
            return;
        }

        $settingRunning->setting_value = true;
        $settingRunning->save();

        Carbon::setLocale(env('APP_LOCALE'));

        $this->runExecutor(new GridFinisherExecutor);
        $this->runExecutor(new GridNotifierExecutor);
        $this->runExecutor(new TipsExecutor);
        $this->runExecutor(new CountdownExecutor);
        $this->runExecutor(new CheckAccountExecutor);

        /** @var Event $eventsQuery */
        $eventsQuery = Event::query();
        $eventsQuery->filterPendings();
        $events = $eventsQuery->get();

        /** @var Event $event */
        foreach ($events as $event) {
            try {
                $this->runExecutor($event->executor(), $event->reference);
            }
            catch (Exception $exception) {
                $event->setAttribute('event_failures', $event->event_failures + 1);
                $event->save();

                continue;
            }

            $event->event_executed = Carbon::now();
            $event->save();
        }

        $settingRunning->setting_value = false;
        $settingRunning->save();
    }

    /**
     * Run an Executor instance.
     * @param Executor   $executor  Executor instance.
     * @param Model|null $reference Event reference.
     */
    private function runExecutor(Executor $executor, ?Model $reference = null): void
    {
        printf('Running "%s"... ', get_class($executor));

        try {
            $executor->run($reference);
            printf("OK\n");
        }
        catch (Exception $exception) {
            printf("FAILED\n");
            throw $exception;
        }
    }
}
