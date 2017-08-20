<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\ForceMomentException;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Services\CommandService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InitializationMoment extends SessionMoment
{
    use ModificationMoment;

    public const PROCESS_CONTINUE = 'continue';
    public const PROCESS_GRID_ID  = 'gridId';

    const REPLY_MODIFY_DURATION     = 'ModifyDuration';
    const REPLY_MODIFY_MANAGERS     = 'ModifyManagers';
    const REPLY_MODIFY_PLAYERS      = 'ModifyPlayers';
    const REPLY_MODIFY_REQUIREMENTS = 'ModifyRequirements';
    const REPLY_MODIFY_SUBTITLE     = 'ModifySubtitle';
    const REPLY_MODIFY_TIMING       = 'ModifyTiming';
    const REPLY_MODIFY_TITLE        = 'ModifyTitle';
    const REPLY_TRANSFER_OWNER      = 'TransferOwner';
    const REPLY_UNSUBSCRIBE         = 'Unsubscribe';

    /**
     * @inheritdoc
     */
    public function validateInitialization(Update $update, Process $process): bool
    {
        if ($process->get(self::PROCESS_CONTINUE)) {
            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_GRID_SHOW_SHORT)) {
            $command          = $update->message->getCommand();
            $commandArguments = $command->arguments;

            if ($commandArguments->count() < 1) {
                return false;
            }

            $botService = BotService::getInstance();

            /** @var Builder $gridQuery */
            /** @var Grid $grid */
            $gridQuery = Grid::query();
            $gridQuery->with('subscribers.gamertag');
            $grid = $gridQuery->find($commandArguments[0]);

            if (!$grid) {
                $botService->createMessage($update->message)
                    ->appendMessage(trans('GridListing.errorGridNotFound'))
                    ->publish();

                return true;
            }

            $process->offsetSet(self::PROCESS_CONTINUE, true);
            $process->offsetSet(self::PROCESS_GRID_ID, $grid->id);

            switch (Str::upper($command->getArgument(1))) {
                case trans('Command.commands.subscribeTitularCommandLetter'):
                    $this->subscribeAs($update, $process, $grid, GridSubscription::POSITION_TITULAR);

                    return true;
                    break;
                case trans('Command.commands.subscribeTitularReserveCommandLetter'):
                    $this->subscribeAs($update, $process, $grid, GridSubscription::POSITION_TITULAR_RESERVE);

                    return true;
                    break;
                case trans('Command.commands.subscribeReserveCommandLetter'):
                    $this->subscribeAs($update, $process, $grid, GridSubscription::POSITION_RESERVE);

                    return true;
                    break;
            }

            static::notifyOptions($update, $process);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(self::PROCESS_GRID_ID));

        if ($grid->isOwner($update->message->from)) {
            switch ($update->message->text) {
                case self::REPLY_TRANSFER_OWNER:
                    throw new ForceMomentException(TransferOwnerMoment::class);
                    break;
            }
        }

        if ($grid->isManager($update->message->from)) {
            switch ($update->message->text) {
                case self::REPLY_MODIFY_TITLE:
                    throw new ForceMomentException(ModifyTitleMoment::class);
                    break;
                case self::REPLY_MODIFY_SUBTITLE:
                    throw new ForceMomentException(ModifySubtitleMoment::class);
                    break;
                case self::REPLY_MODIFY_REQUIREMENTS:
                    throw new ForceMomentException(ModifyRequirementsMoment::class);
                    break;
                case self::REPLY_MODIFY_TIMING:
                    throw new ForceMomentException(ModifyTimingMoment::class);
                    break;
                case self::REPLY_MODIFY_DURATION:
                    throw new ForceMomentException(ModifyDurationMoment::class);
                    break;
                case self::REPLY_MODIFY_PLAYERS:
                    throw new ForceMomentException(ModifyPlayersMoment::class);
                    break;
                case self::REPLY_MODIFY_MANAGERS:
                    throw new ForceMomentException(ModifyManagersMoment::class);
                    break;
            }
        }

        if ($grid->isSubscriber($update->message->from)) {
            switch ($update->message->text) {
                case self::REPLY_UNSUBSCRIBE:
                    throw new ForceMomentException(UnsubscribeMoment::class);
                    break;
            }
        }

        return null;
    }

    /**
     * Subscribe no grid with a specific rule.
     * @param Update  $update   Update instance.
     * @param Process $process  Process instance.
     * @param Grid    $grid     Grid instance.
     * @param string  $position Subscription position.
     * @throws \Exception
     */
    private function subscribeAs(Update $update, Process $process, Grid $grid, string $position)
    {
        $botService       = BotService::getInstance();
        $userSubscription = $grid->getUserSubscription($update->message->from);

        if ($userSubscription && $userSubscription->subscription_position === $position) {
            $botService->createMessage($update->message)
                ->appendMessage(trans('GridSubscription.alreadySubscribed', [
                    'position' => GridSubscription::getPositionText($userSubscription->subscription_position),
                ]))
                ->publish();

            return;
        }

        if (!$userSubscription) {
            switch ($position) {
                case GridSubscription::POSITION_TITULAR:
                    if ($grid->getVacancies() === 0) {
                        $botService->createMessage($update->message)
                            ->appendMessage(trans('GridSubscription.errorNoVacancies'))
                            ->publish();
                    }
                    break;
            }

            $userSubscription                        = new GridSubscription;
            $userSubscription->grid_id               = $grid->id;
            $userSubscription->gamertag_id           = $update->message->from->getUserRegister()->gamertag->id;
            $userSubscription->subscription_rule     = GridSubscription::RULE_USER;
            $userSubscription->subscription_position = $position;
            $userSubscription->reserved_at           = Carbon::now();
            $userSubscription->save();

            static::notifyOptions($update, $process);

            return;
        }

        if ($position === GridSubscription::POSITION_TITULAR_RESERVE &&
            $userSubscription->subscription_position === GridSubscription::POSITION_TITULAR) {
            $botService->createMessage($update->message)
                ->appendMessage(trans('GridSubscription.errorAlreadyTitular'))
                ->publish();

            return;
        }

        if ($position === GridSubscription::POSITION_TITULAR_RESERVE &&
            $grid->getVacancies() > 0) {
            $botService->createMessage($update->message)
                ->appendMessage(trans('GridSubscription.errorVacanciesAvailable'))
                ->publish();

            return;
        }

        $userSubscription->subscription_position = $position;
        $userSubscription->reserved_at           = Carbon::now();
        $userSubscription->save();

        static::notifyOptions($update, $process);
    }
}
