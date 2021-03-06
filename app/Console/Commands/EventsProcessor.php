<?php

declare(strict_types = 1);

namespace Application\Console\Commands;

use Application\Events\CheckAccountExecutor;
use Application\Events\CheckActivitiesExecutor;
use Application\Events\CheckClanExecutor;
use Application\Events\Executor;
use Application\Events\GridFinisherExecutor;
use Application\Events\GridNotifierExecutor;
use Application\Events\GridPlayingExecutor;
use Application\Events\GridRespawnExecutor;
use Application\Events\TipsExecutor;
use Application\Exceptions\Executor\KeepWorkingException;
use Application\Services\SettingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class EventsProcessor extends Command
{
    public const SETTING_RUNNING = 'running';

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
     * @throws Exception
     */
    public function handle()
    {
        $settingRunning = SettingService::fromReference($this, self::SETTING_RUNNING);

        if ($settingRunning->setting_value === true) {
            return;
        }

        $settingRunning->setting_value = true;
        $settingRunning->save();

        Carbon::setLocale(env('APP_LOCALE'));

        $this->runExecutor(new GridPlayingExecutor);
        $this->runExecutor(new GridFinisherExecutor);
        $this->runExecutor(new GridNotifierExecutor);
        $this->runExecutor(new GridRespawnExecutor);
        $this->runExecutor(new TipsExecutor);
        $this->runExecutor(new CheckAccountExecutor);
        // $this->runExecutor(new CheckStatsExecutor);
        $this->runExecutor(new CheckClanExecutor);
        $this->runExecutor(new CheckActivitiesExecutor);

        $settingRunning->setting_value = false;
        $settingRunning->save();
    }

    /**
     * Run an Executor instance.
     * @param Executor $executor Executor instance.
     * @throws Exception
     */
    private function runExecutor(Executor $executor): void
    {
        printf('Running "%s"... ', get_class($executor));

        try {
            $executor->run();
            printf("OK\n");
        }
        catch (KeepWorkingException $exception) {
            printf("OK\n");
            $this->runExecutor($executor);
        }
        catch (Exception $exception) {
            printf("FAILED\n");
            throw $exception;
        }
    }
}
