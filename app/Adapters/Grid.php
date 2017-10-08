<?php

declare(strict_types = 1);

namespace Application\Adapters;

use Application\Adapters\Ranking\PlayerRanking;
use Application\Adapters\Telegram\User;
use Application\Models\Grid as GridModel;
use Application\Models\GridSubscription;
use Application\Services\FormattingService;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Services\UserExperienceService;
use Application\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;

/**
 * @property string      $title        Grid title.
 * @property string|null $subtitle     Grid subtitle.
 * @property string|null $requirements Grid requirements.
 * @property Carbon      $timing       Grid timing.
 * @property float       $duration     Grid duration.
 * @property int         $players      Grid players.
 * @property User        $owner        Grid owner.
 * @property int|null    $grid_id      Grid id reference.
 */
class Grid extends BaseFluent
{
    public const STRUCTURE_TYPE_EXAMPLE = 'example';
    public const STRUCTURE_TYPE_FULL    = 'full';

    /**
     * Returns a Grid adapter from a Grid model.
     * @param GridModel $grid Grid model instance.
     * @return Grid
     */
    public static function fromModel(GridModel $grid): Grid
    {
        return new self([
            'title'        => $grid->grid_title,
            'subtitle'     => $grid->grid_subtitle,
            'requirements' => $grid->grid_requirements,
            'timing'       => $grid->grid_timing,
            'duration'     => $grid->getDurationAsFloat(),
            'grid_id'      => $grid->id,
        ]);
    }

    /**
     * Return the grid duration formatted.
     * @return string|null
     */
    public function getDuration(): ?string
    {
        $durationArray            = [];
        $durationHours            = (int) $this->duration;
        $durationArray['hours']   = trans_choice('Grid.durationHours', $durationHours, [ 'hours' => $durationHours ]);
        $durationMinutes          = (int) round(fmod((float) $this->duration, 1) * 60);
        $durationArray['minutes'] = trans_choice('Grid.durationMinutes', $durationMinutes, [ 'minutes' => $durationMinutes ]);
        $durationCount            = count(array_filter($durationArray));

        if ($durationCount !== 0) {
            if ($durationMinutes === 0) {
                return array_first($durationArray);
            }

            if ($durationHours === 0) {
                return array_last($durationArray);
            }

            return trans('Grid.durationBoth', $durationArray);
        }

        return null;
    }

    /**
     * Returns how much time need wait to Grid start.
     * Minimum value is "0 minute" (even for negative values).
     * @return string
     */
    public function getMinutesDistance(): string
    {
        $diffInMinutes = max(0, Carbon::now()->diffInMinutes($this->timing, false));

        return trans_choice('Grid.durationMinutes', $diffInMinutes, [ 'minutes' => $diffInMinutes ]);
    }

    /**
     * Return the grid structure.
     * @param string $structureType Structure type (STRUCTURE_TYPE consts).
     * @return string
     */
    public function getStructure(string $structureType): string
    {
        $grid     = null;
        $gridIcon = null;

        if ($this->grid_id) {
            /** @var Builder $gridQuery */
            /** @var GridModel $grid */
            $gridQuery = GridModel::query();
            $gridQuery->with('subscribers.gamertag');
            $grid = $gridQuery->find($this->grid_id);

            $gridStatusText = $grid->getStatusText();

            /** @var Translator $trans */
            $trans = app('translator');

            if ($gridStatusText !== null &&
                $trans->has('Grid.statusIcon' . Str::ucfirst($gridStatusText))) {
                $gridIcon = $gridStatusText;
            }
        }

        $result = trans('Grid.header', [ 'title' => $this->getTitle() ]);

        if ($gridIcon !== null) {
            $result = trans('Grid.headerIconWrapper', [
                'icon'   => trans('Grid.statusIcon' . Str::ucfirst($gridIcon)),
                'header' => rtrim($result, "\n"),
            ]);
        }

        if ($structureType === self::STRUCTURE_TYPE_EXAMPLE) {
            /** @var UserService $userService */
            $userService = MockupService::getInstance()->instance(UserService::class);
            $user        = $userService->get($this->owner->id);

            assert($user !== null);

            $result .= trans('Grid.gridOwner', [ 'value' => $user->gamertag->gamertag_value ]);
        }

        if ($grid && $structureType === self::STRUCTURE_TYPE_FULL) {
            $gridStatusDetail = null;

            if ($grid->isCanceled()) {
                $gridStatusDetail = trans('Grid.gridStatusDetails', [
                    'details' => $grid->getCancelReason(),
                ]);
            }

            $result .= trans('Grid.gridStatus', [
                'value'   => trans('Grid.status' . Str::ucfirst($grid->grid_status)),
                'details' => $gridStatusDetail,
            ]);
        }

        $result .= trans('Grid.gridTiming', [
            'value' => $this->getTiming(),
        ]);

        if (!$grid || !$grid->isCanceled()) {
            $result .= trans('Grid.gridDuration', [
                'value' => $this->getDuration(),
            ]);
        }

        if ($this->requirements) {
            $result .= trans('Grid.gridRequirements', [ 'value' => $this->requirements ]);
        }

        if ($structureType === self::STRUCTURE_TYPE_EXAMPLE) {
            $result .= trans('Grid.gridPlayers', [ 'value' => $this->players ]);
        }

        if ($grid && $structureType === self::STRUCTURE_TYPE_FULL && !$grid->isCanceled()) {
            $resultTitulars = [];
            $resultReserves = [];

            $botService = BotService::getInstance();

            $globalRanking = UserExperienceService::getInstance()->getGlobalRanking();

            /** @var GridSubscription $gridSubscriber */
            foreach ($grid->subscribers_sorted as $gridSubscriber) {
                $playerIcon = sprintf('%s`%s`',
                    trans('Ranking.iconStarter'),
                    FormattingService::toSuperscript('0'));

                if ($globalRanking->has($gridSubscriber->gamertag->user_id)) {
                    /** @var PlayerRanking $playerRanking */
                    $playerRanking = $globalRanking->get($gridSubscriber->gamertag->user_id);
                    $playerIcon    = $playerRanking->getLevel()->getIconTitle();
                }

                $gridSubscriberMask = [
                    'playerIcon' => $playerIcon,
                    'gamertag'   => $botService->escape($gridSubscriber->gamertag->gamertag_value),
                    'icon'       => implode(' ', $gridSubscriber->getIcons()),
                ];

                if ($gridSubscriber->subscription_description) {
                    $gridSubscriberMask['gamertag'] = trans('Grid.subscriberObservation', [
                        'playerIcon'  => $playerIcon,
                        'gamertag'    => $gridSubscriberMask['gamertag'],
                        'observation' => $botService->escape($gridSubscriber->subscription_description),
                    ]);
                }

                if ($gridSubscriber->isTitular()) {
                    $resultTitulars[] = trans('Grid.titularItem', $gridSubscriberMask);
                    continue;
                }

                $resultReserves [] = trans('Grid.reserveItem', $gridSubscriberMask);
            }

            $result .= trans('Grid.titularsHeader');
            $result .= implode(array_pad($resultTitulars, $grid->grid_players, trans('Grid.titularItemEmpty')));

            if ($resultReserves) {
                $result .= trans('Grid.reservesHeader');
                $result .= implode($resultReserves);
            }
        }

        return $result;
    }

    /**
     * Returns the timing formatted.
     * @param bool|null $fulltime Show full time, else just hour (default: true).
     * @return string
     */
    public function getTiming(?bool $fulltime = null): string
    {
        $timingNow  = Carbon::now()->second(0);
        $timingHour = $this->timing->format('H:i');

        if ($fulltime === false) {
            return $timingHour;
        }

        if ($timingNow->day !== $this->timing->day) {
            return trans('Grid.timingTomorrow', [
                'day'    => $this->timing->format('d/m'),
                'timing' => $timingHour,
            ]);
        }

        return trans('Grid.timingToday', [ 'timing' => $timingHour ]);
    }

    /**
     * Returns this Grid title formatted.
     */
    public function getTitle(): string
    {
        $headerSubtitle = $this->subtitle ? trans('Grid.headerSubtitle', [ 'subtitle' => $this->subtitle ]) : null;

        return trans('Grid.headerBase', [
            'title'    => $this->title,
            'subtitle' => $headerSubtitle,
        ]);
    }
}
