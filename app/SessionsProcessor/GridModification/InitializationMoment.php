<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\ForceMomentException;
use Application\Models\Grid;
use Application\Services\CommandService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;
use Illuminate\Database\Eloquent\Builder;

class InitializationMoment extends SessionMoment
{
    use ModificationMoment;

    public const PROCESS_CONTINUE = 'continue';
    public const PROCESS_GRID     = 'grid';

    const REPLY_MODIFY_DURATION     = 'ModifyDuration';
    const REPLY_MODIFY_PLAYERS      = 'ModifyPlayers';
    const REPLY_MODIFY_REQUIREMENTS = 'ModifyRequirements';
    const REPLY_MODIFY_SUBTITLE     = 'ModifySubtitle';
    const REPLY_MODIFY_TIMING       = 'ModifyTiming';
    const REPLY_MODIFY_TITLE        = 'ModifyTitle';
    const REPLY_TRANSFER_OWNER      = 'TransferOwner';

    /**
     * @inheritdoc
     */
    public function validateInitialization(Update $update, Process $process): bool
    {
        if ($process->get(self::PROCESS_CONTINUE)) {
            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_MY_GRID_SHOW_SHORT)) {
            $commandArguments = $update->message->getCommand()->arguments;

            if (count($commandArguments) < 1) {
                return false;
            }

            $botService = BotService::getInstance();

            /** @var Builder $gridQuery */
            /** @var Grid $grid */
            $gridQuery = Grid::query();
            $gridQuery->with('subscribers.gamertag');
            $grid = $gridQuery->find($commandArguments[0]);

            if (!$grid) {
                $botService->sendMessage($update->message->chat->id, trans('GridListing.errorGridNotFound'));

                return true;
            }

            $process->offsetSet(self::PROCESS_CONTINUE, true);
            $process->offsetSet(self::PROCESS_GRID, $grid);

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
            case self::REPLY_TRANSFER_OWNER:
                throw new ForceMomentException(TransferOwnerMoment::class);
                break;
        }

        return null;
    }
}
