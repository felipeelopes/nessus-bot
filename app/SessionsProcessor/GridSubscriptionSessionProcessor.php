<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor;

use Application\Adapters\Grid;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid as GridModel;
use Application\Models\GridSubscription;
use Application\Services\CommandService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\Definition\SessionProcessor;
use Application\Types\Process;
use Carbon\Carbon;

class GridSubscriptionSessionProcessor extends SessionProcessor
{
    private const MAX_OBSERVATIONS = 400;
    private const MAX_PLAYERS      = 12;
    private const MAX_SUBTITLE     = 20;
    private const MAX_TITLE        = 80;

    public const  MOMENT_ACCEPTED                        = 'accepted';
    private const MOMENT_CREATION_CHECK_CONFIRM_CREATION = 'creationCheckConfirm';
    private const MOMENT_CREATION_CHECK_PLAYERS          = 'creationCheckPlayers';
    private const MOMENT_CREATION_CHECK_SUBTITLE         = 'creationCheckSubtitle';
    private const MOMENT_CREATION_CHECK_TIMING           = 'creationCheckTiming';
    private const MOMENT_CREATION_CHECK_TIMING_CONFIRM   = 'creationCheckTimingConfirm';
    private const MOMENT_CREATION_CHECK_TITLE            = 'creationCheckTitle';
    private const MOMENT_CREATION_PUBLISH                = 'creationPublish';
    private const MOMENT_WELCOME                         = 'welcome';

    private const PROCESS_GRID         = 'grid';
    private const PROCESS_PLAYERS      = 'players';
    private const PROCESS_REQUIREMENTS = 'requirements';
    private const PROCESS_SUBTITLE     = 'subtitle';
    private const PROCESS_TIMING       = 'timing';
    private const PROCESS_TITLE        = 'title';

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->register(self::MOMENT_WELCOME, [ $this, 'momentWelcome' ]);
        $this->register(self::MOMENT_CREATION_CHECK_TITLE, [ $this, 'momentCreationCheckTitle' ]);
        $this->register(self::MOMENT_CREATION_CHECK_SUBTITLE, [ $this, 'momentCreationCheckSubtitle' ]);
        $this->register(self::MOMENT_CREATION_CHECK_TIMING, [ $this, 'momentCreationCheckTiming' ]);
        $this->register(self::MOMENT_CREATION_CHECK_TIMING_CONFIRM, [ $this, 'momentCreationCheckTimingConfirm' ]);
        $this->register(self::MOMENT_CREATION_CHECK_PLAYERS, [ $this, 'momentCreationCheckPlayers' ]);
        $this->register(self::MOMENT_CREATION_CHECK_CONFIRM_CREATION, [ $this, 'momentCreationConfirmCreation' ]);
        $this->register(self::MOMENT_CREATION_PUBLISH, [ $this, 'momentCreationPublish' ]);
    }

    /**
     * Check the players number.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationCheckPlayers(Update $update, Process $process): ?string
    {
        $message = $update->message->text;

        if ($message !== trans('GridSubscription.creationWizardTimingConfirmYes')) {
            return $this->validateTiming($update, $process)
                ?: static::MOMENT_CREATION_CHECK_PLAYERS;
        }

        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardPlayers', [
                'max' => self::MAX_PLAYERS,
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardPlayersOptions'))
        );

        return static::MOMENT_CREATION_CHECK_CONFIRM_CREATION;
    }

    /**
     * Check the subtitle.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationCheckSubtitle(Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if (strlen($message) > self::MAX_SUBTITLE) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorSubtitleTooLong', [ 'length' => self::MAX_SUBTITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardSubtitleOptions'))
            );

            return self::MOMENT_CREATION_CHECK_SUBTITLE;
        }

        $process->put(self::PROCESS_SUBTITLE, $message);

        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardObservations'),
            PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardObservationsOptions'))
        );

        return self::MOMENT_CREATION_CHECK_TIMING;
    }

    /**
     * Check the timing.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationCheckTiming(Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if (strlen($message) > self::MAX_OBSERVATIONS) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorObservationsTooLong', [
                    'length' => self::MAX_OBSERVATIONS,
                ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardObservationsOptions'))
            );

            return self::MOMENT_CREATION_CHECK_TIMING;
        }

        $process->put(self::PROCESS_REQUIREMENTS, $message);

        $botService->sendCancelableMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardTiming')
        );

        return self::MOMENT_CREATION_CHECK_TIMING_CONFIRM;
    }

    /**
     * Check the timing confirmation.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationCheckTimingConfirm(Update $update, Process $process): ?string
    {
        $validateTiming = $this->validateTiming($update, $process);

        if ($validateTiming !== null) {
            return $validateTiming;
        }

        return static::MOMENT_CREATION_CHECK_PLAYERS;
    }

    /**
     * Check the title.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationCheckTitle(Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if (strlen($message) > self::MAX_TITLE) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorTitleTooLong', [ 'length' => self::MAX_TITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardOptions'))
            );

            return self::MOMENT_CREATION_CHECK_TITLE;
        }

        $process->put(self::PROCESS_TITLE, $message);

        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardSubtitle'),
            PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardSubtitleOptions'))
        );

        return self::MOMENT_CREATION_CHECK_SUBTITLE;
    }

    /**
     * Confirm the creation.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationConfirmCreation(Update $update, Process $process): ?string
    {
        $botService   = BotService::getInstance();
        $playersCount = (int) $update->message->text;

        if (!$playersCount) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorPlayersInvalid', [ 'max' => self::MAX_PLAYERS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardPlayersOptions'))
            );

            return self::MOMENT_CREATION_CHECK_CONFIRM_CREATION;
        }

        if ($playersCount < 2) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorPlayersTooFew', [ 'max' => self::MAX_PLAYERS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardPlayersOptions'))
            );

            return self::MOMENT_CREATION_CHECK_CONFIRM_CREATION;
        }

        if ($playersCount > self::MAX_PLAYERS) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorPlayersTooMuch', [ 'max' => self::MAX_PLAYERS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardPlayersOptions'))
            );

            return self::MOMENT_CREATION_CHECK_CONFIRM_CREATION;
        }

        $process->put(self::PROCESS_PLAYERS, $playersCount);

        $processGrid = new Grid([
            'title'        => $process->get(self::PROCESS_TITLE),
            'subtitle'     => $process->get(self::PROCESS_SUBTITLE),
            'requirements' => $process->get(self::PROCESS_REQUIREMENTS),
            'players'      => $process->get(self::PROCESS_PLAYERS),
            'timing'       => $process->get(self::PROCESS_TIMING),
            'owner'        => $update->message->from,
        ]);

        $process->offsetSet(self::PROCESS_GRID, $processGrid);

        $botService->sendMessage(
            $update->message->from->id,
            $processGrid->getStructure(Grid::STRUCTURE_TYPE_EXAMPLE)
        );
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardConfirmCreationHeader'),
            PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardConfirmCreationOptions'))
        );

        return static::MOMENT_CREATION_PUBLISH;
    }

    /**
     * Publish the grid.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentCreationPublish(Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if ($message !== trans('GridSubscription.creationWizardConfirmCreationYes')) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.errorPublishInvalid'),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardConfirmCreationOptions'))
            );

            return self::MOMENT_CREATION_PUBLISH;
        }

        $user = UserService::getInstance()->get($update->message->from->id);

        $grid                    = new GridModel;
        $grid->gamertag_id       = $user->getGamertag()->id;
        $grid->grid_title        = $process->get(self::PROCESS_TITLE);
        $grid->grid_subtitle     = $process->get(self::PROCESS_SUBTITLE);
        $grid->grid_requirements = $process->get(self::PROCESS_REQUIREMENTS);
        $grid->grid_players      = $process->get(self::PROCESS_PLAYERS);
        $grid->grid_timing       = $process->get(self::PROCESS_TIMING);
        $grid->save();

        $gridSubscription                           = new GridSubscription;
        $gridSubscription->grid_id                  = $grid->id;
        $gridSubscription->gamertag_id              = $user->getGamertag()->id;
        $gridSubscription->subscription_description = null;
        $gridSubscription->subscription_rule        = GridSubscription::RULE_OWNER;
        $gridSubscription->save();

        /** @var Grid $processGrid */
        $processGrid          = $process->offsetGet(self::PROCESS_GRID);
        $processGrid->grid_id = $grid->id;

        $botService->sendPublicMessage($processGrid->getStructure(Grid::STRUCTURE_TYPE_FULL));
        $botService->sendMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardPublished')
        );

        return static::MOMENT_ACCEPTED;
    }

    /**
     * Initialize the Grid Subscription wizard.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return null|string
     */
    public function momentWelcome(Update $update, Process $process): ?string
    {
        if ($update->message->isCommand(CommandService::COMMAND_NEW_GRID)) {
            $process->clear();

            $botService = BotService::getInstance();

            $botService->notifyPrivateMessage($update->message);
            $botService->sendMessage(
                $update->message->from->id,
                trans('GridSubscription.creationBeta')
            );
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridSubscription.creationWizard'),
                PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardOptions'))
            );

            return self::MOMENT_CREATION_CHECK_TITLE;
        }

        return null;
    }

    /**
     * Validate the timing.
     * @param Update  $update  Update instance.
     * @param Process $process Process collection.
     * @return string
     */
    private function validateTiming(Update $update, Process $process): string
    {
        $botService = BotService::getInstance();
        $format     = trim(preg_replace('/\D+/', ' ', $update->message->text));

        if ($format === '' || substr_count($format, ' ') > 1) {
            $botService->sendMessage(
                $update->message->from->id,
                trans('GridSubscription.errorTimingInvalid')
            );

            return self::MOMENT_CREATION_CHECK_TIMING_CONFIRM;
        }

        if (strpos($format, ' ') === false) {
            $format .= ' 00';
        }

        [ $timingHour, $timingMinutes ] = array_pad(array_map('intval', explode(' ', $format)), 2, 0);

        if ($timingHour === 24 && $timingMinutes === 0) {
            $timingHour = 0;
        }

        if ($timingHour > 23 || $timingMinutes > 59) {
            $botService->sendMessage(
                $update->message->from->id,
                trans('GridSubscription.errorTimingInvalid')
            );

            return self::MOMENT_CREATION_CHECK_TIMING_CONFIRM;
        }

        $timingNow    = Carbon::now()->second(0);
        $timingCarbon = $timingNow->copy()->setTime($timingHour, $timingMinutes);
        $timingDiff   = $timingNow->diffInSeconds($timingCarbon, false);

        if ($timingDiff >= 0 && $timingDiff < 600) {
            $botService->sendMessage(
                $update->message->from->id,
                trans('GridSubscription.errorTimingTooShort')
            );

            return self::MOMENT_CREATION_CHECK_TIMING_CONFIRM;
        }

        $timingToday   = $timingDiff > 0;
        $timingMessage = null;

        if ($timingToday) {
            $timingMessage = trans('GridSubscription.creationWizardTimingConfirmToday', [
                'timing' => $timingCarbon->format('H:i'),
            ]);
        }
        else {
            $timingCarbon->addDay();
            $timingMessage = trans('GridSubscription.creationWizardTimingConfirmTomorrow', [
                'day'    => $timingCarbon->format('d/m'),
                'timing' => $timingCarbon->format('H:i'),
            ]);
        }

        $process->put(self::PROCESS_TIMING, $timingCarbon);

        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridSubscription.creationWizardTimingConfirm', [
                'timing' => $timingMessage,
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridSubscription.creationWizardTimingConfirmOptions'))
        );

        return static::MOMENT_CREATION_CHECK_PLAYERS;
    }
}
