<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Events\CheckStatsExecutor;
use Application\Models\Stat;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserStatsStrategy implements UserStrategyContract
{
    /**
     * Returns the top Ranking.
     * @param Collection|array $stats
     * @param Collection|array $secondsPlayedStats
     * @return Collection
     */
    public static function getTopRanking(?Collection &$stats = null, ?Collection &$secondsPlayedStats = null): Collection
    {
        $statsTypes           = Stat::getStatsTypes();
        $statsTypesNames      = $statsTypes->keys();
        $statsTypesOrderDesc  = $statsTypes->where('order', Stat::ORDER_DESC)->keys();
        $statsTypesModesDaily = $statsTypes->where('mode', Stat::MODE_DAILY)->keys();

        $statsQuery = Stat::query();
        $statsQuery->with('user.gamertag');
        $statsQuery->whereIn('stat_name', $statsTypesNames);
        $statsQuery->where('updated_at', '>=', Carbon::now()->subDays(3));
        $stats = $statsQuery->get();

        $secondsPlayedStats = $stats->where('stat_name', 'secondsPlayed')
            ->pluck('stat_value', 'user_id');

        return $stats
            ->each(function (Stat $stat) use ($secondsPlayedStats, $statsTypesModesDaily) {
                if ($statsTypesModesDaily->contains($stat->stat_name)) {
                    $stat->stat_value = $stat->stat_value * 14400 / $secondsPlayedStats->get($stat->user_id);
                }
            })
            ->groupBy('stat_name')
            ->map(function (Collection $topStats) use ($statsTypesOrderDesc) {
                return $statsTypesOrderDesc->contains($topStats->first()->stat_name)
                    ? $topStats->sortBy('stat_value')->first()
                    : $topStats->sortBy('stat_value')->last();
            });
    }

    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            return null;
        }

        if ($update->message->isCommand(CommandService::COMMAND_STATS)) {
            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($this->generateStats())
                ->unduplicate(self::class . '@Command:' . CommandService::COMMAND_STATS)
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_SELF_STATS)) {
            $messageEntityBotCommand = $update->message->getCommand();
            $whichRequestTrans       = trans('Stats.selfStatsRequest');

            $mentionedUser = null;
            if ($messageEntityBotCommand) {
                $mentionedUser = array_first($messageEntityBotCommand->getMentions());
                if ($mentionedUser) {
                    $user              = $mentionedUser;
                    $whichRequestTrans = trans('Stats.userStatsRequest');
                }
            }

            $identifier = self::class . '@Command:' . CommandService::COMMAND_SELF_STATS . '@' . $user->id;

            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($whichRequestTrans)
                ->unduplicate($identifier)
                ->publish();

            CheckStatsExecutor::requestStats($user);

            $botMessageService = BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($this->generateStats($user))
                ->unduplicate($identifier);

            $isPublic = $messageEntityBotCommand &&
                        $messageEntityBotCommand->getTextArgument() === 'public';

            if (!$isPublic && !$mentionedUser) {
                $botMessageService->forcePrivate();
            }

            $botMessageService->publish();

            return true;
        }

        return null;
    }

    /**
     * Generate game stats.
     */
    private function generateStats(?User $user = null)
    {
        /** @var Collection|Stat[] $stats */
        $topStats = static::getTopRanking($stats, $secondsPlayedStats);

        $newestStatQuery = Stat::query();
        $newestStatQuery->orderBy('updated_at', 'desc');
        $newestStat = $newestStatQuery->first();

        assert($newestStat !== null);

        $statsTypes          = Stat::getStatsTypes();
        $statsTypesOrderDesc = $statsTypes->where('order', Stat::ORDER_DESC)->keys();

        $contents      = [];
        $previousGroup = null;
        $totalPoints   = 0;

        $userStats = $user !== null
            ? $stats
                ->where('user_id', $user->id)
                ->keyBy('stat_name')
            : null;

        $statsTypes = Stat::getStatsTypes()
            ->whereIn('name', $topStats->keys());

        foreach ($statsTypes as $statsKey => $statsType) {
            if ($userStats !== null &&
                !$userStats->has($statsType['name'])) {
                continue;
            }

            if ($statsType['group'] !== $previousGroup) {
                $previousGroup = $statsType['group'];
                $contents[]    = trans('Stats.statsGroup', [ 'title' => $statsType['group'] ]);
            }

            /** @var Stat $topStat */
            $topStat = $topStats->get($statsKey);

            if ($userStats !== null) {
                /** @var Stat|null $userStat */
                $userStat = $userStats->get($statsKey);

                if ($userStat === null) {
                    continue;
                }

                $userPercent = $userStat->getPercentFrom($topStat->stat_value, $statsTypesOrderDesc->contains($statsKey));
                $totalPoints += $userPercent;

                $contents[] = trans('Stats.statsItemSelf', [
                    'percent' => str_pad(sprintf('%.1f%%', $userPercent * 100), 6, ' ', STR_PAD_LEFT),
                    'title'   => $statsType['title'],
                    'value'   => $userStat->getFormattedValue() ?: '-',
                ]);

                continue;
            }

            $contents[] = trans('Stats.statsItem', [
                'title'    => $statsType['title'],
                'value'    => $topStat->getFormattedValue() ?: '-',
                'gamertag' => $topStat->user->gamertag->gamertag_value ?: '-',
            ]);
        }

        if ($user !== null && $user->gamertag) {
            return trans('Stats.statsHeaderSelf', [
                'gamertag' => $user->gamertag->gamertag_value,
                'contents' => implode($contents),
                'points'   => sprintf('%.1f', $totalPoints * 100),
            ]);
        }

        return trans('Stats.statsHeader', [
            'contents' => implode($contents),
            'datetime' => $newestStat->updated_at ?: '-',
        ]);
    }
}
