<?php

declare(strict_types = 1);

namespace Application\Adapters;

use Application\Adapters\Telegram\User;
use Application\Services\MockupService;
use Application\Services\UserService;
use Carbon\Carbon;

/**
 * @property string      $title        Grid title.
 * @property string|null $subtitle     Grid subtitle.
 * @property string|null $observations Grid observations.
 * @property Carbon      $timing       Grid timing.
 * @property int         $players      Grid players.
 * @property User        $owner        Grid owner.
 */
class Grid extends BaseFluent
{
    public const STRUCTURE_TYPE_EXAMPLE = 'example';
    public const STRUCTURE_TYPE_FULL    = 'full';

    /**
     * Return the grid structure.
     * @param string $structureType Structure type (STRUCTURE_TYPE consts).
     * @return string
     */
    public function getStructure(string $structureType): string
    {
        $result = trans('Grid.header', [
            'title'    => $this->title,
            'subtitle' => $this->subtitle
                ? trans('Grid.headerSubtitle', [ 'subtitle' => $this->subtitle ])
                : null,
        ]);

        /** @var UserService $userService */
        $userService = MockupService::getInstance()->instance(UserService::class);
        $user        = $userService->get($this->owner->id);

        if ($structureType === self::STRUCTURE_TYPE_EXAMPLE) {
            $result .= trans('Grid.gridOwner', [ 'value' => $user->getGamertag()->gamertag_value ]);
        }

        if ($this->observations) {
            $result .= trans('Grid.gridObservations', [ 'value' => $this->observations ]);
        }

        $timingNow  = Carbon::now()->second(0);
        $timingHour = $this->timing->format('H:i');

        if ($timingNow->day !== $this->timing->day) {
            $result .= trans('Grid.gridTiming', [
                'value' => trans('Grid.timingTomorrow', [
                    'day'    => $this->timing->format('d/m'),
                    'timing' => $timingHour,
                ]),
            ]);
        }
        else {
            $result .= trans('Grid.gridTiming', [
                'value' => trans('Grid.timingToday', [ 'timing' => $timingHour ]),
            ]);
        }

        if ($structureType === self::STRUCTURE_TYPE_EXAMPLE) {
            $result .= trans('Grid.gridPlayers', [ 'value' => $this->players ]);
        }

        if ($structureType === self::STRUCTURE_TYPE_FULL) {
            $result .= trans('Grid.titularsHeader');

            $ownerUser = $this->owner->getUserRegister();

            $result .= trans('Grid.titularItem', [
                'gamertag' => $ownerUser->getGamertag()->gamertag_value,
                'icon'     => trans('Grid.typeOwner'),
            ]);

            for ($i = 1; $i < $this->players; $i++) {
                $result .= trans('Grid.titularItem', [
                    'gamertag' => '',
                    'icon'     => '',
                ]);
            }

            $result .= trans('Grid.reservesHeader');
            $result .= trans('Grid.reserveItem');
        }

        return $result;
    }
}
