<?php

declare(strict_types = 1);

namespace Application\Adapters\Ranking;

use Application\Adapters\BaseFluent;
use Application\Models\User;
use Illuminate\Support\Collection;

/**
 * @property int   $user_id           User ID.
 * @property int   $player_activities Player activities.
 * @property float $player_timing     Played hours.
 * @property int   $player_light      Player max light.
 * @property float $player_experience Player experience.
 * @property int   $player_interation Player interation.
 * @property int   $player_register   Player registration diff days.
 */
class PlayerRanking extends BaseFluent
{
    /**
     * Returns a Collection from Player Ranking from query results.
     * @return Collection|self[]
     */
    public static function fromQuery(array $results): Collection
    {
        return (new Collection(array_map(function ($playerRanking) {
            return new self($playerRanking);
        }, $results)))->keyBy('user_id');
    }

    /**
     * Get all available levels.
     * @return Collection
     */
    public static function getLevels(): Collection
    {
        $iconStarter   = trans('Ranking.iconStarter');
        $iconDecorated = trans('Ranking.iconDecorated');
        $iconBronze    = trans('Ranking.iconBronze');
        $iconSilver    = trans('Ranking.iconSilver');
        $iconGold      = trans('Ranking.iconGold');
        $iconDiamond   = trans('Ranking.iconDiamond');

        $titleStarter   = trans('Ranking.titleStarter');
        $titleDecorated = trans('Ranking.titleDecorated');
        $titleBronze    = trans('Ranking.titleBronze');
        $titleSilver    = trans('Ranking.titleSilver');
        $titleGold      = trans('Ranking.titleGold');
        $titleDiamond   = trans('Ranking.titleDiamond');

        return new Collection([
            // Starter.
            [
                'level'      => 1,
                'xpBase'     => [ 0, 1000 ],
                'icon'       => $iconStarter,
                'title'      => $titleStarter,
                'titleLevel' => [ 1, 3 ],
            ],
            [
                'level'      => 2,
                'xpBase'     => [ 1000, 2000 ],
                'icon'       => $iconStarter,
                'title'      => $titleStarter,
                'titleLevel' => [ 2, 3 ],
            ],
            [
                'level'      => 3,
                'xpBase'     => [ 2000, 4000 ],
                'icon'       => $iconStarter,
                'title'      => $titleStarter,
                'titleLevel' => [ 3, 3 ],
            ],
            // Decorated.
            [
                'level'      => 4,
                'xpBase'     => [ 4000, 6000 ],
                'icon'       => $iconDecorated,
                'title'      => $titleDecorated,
                'titleLevel' => [ 1, 3 ],
            ],
            [
                'level'      => 5,
                'xpBase'     => [ 6000, 8000 ],
                'icon'       => $iconDecorated,
                'title'      => $titleDecorated,
                'titleLevel' => [ 2, 3 ],
            ],
            [
                'level'      => 6,
                'xpBase'     => [ 8000, 10000 ],
                'icon'       => $iconDecorated,
                'title'      => $titleDecorated,
                'titleLevel' => [ 3, 3 ],
            ],
            // Bronze
            [
                'level'      => 7,
                'xpBase'     => [ 10000, 15000 ],
                'icon'       => $iconBronze,
                'title'      => $titleBronze,
                'titleLevel' => [ 1, 2 ],
            ],
            [
                'level'      => 8,
                'xpBase'     => [ 15000, 20000 ],
                'icon'       => $iconBronze,
                'title'      => $titleBronze,
                'titleLevel' => [ 2, 2 ],
            ],
            // Silver
            [
                'level'      => 9,
                'xpBase'     => [ 20000, 23000 ],
                'icon'       => $iconSilver,
                'title'      => $titleSilver,
                'titleLevel' => [ 1, 3 ],
            ],
            [
                'level'      => 10,
                'xpBase'     => [ 23000, 26000 ],
                'icon'       => $iconSilver,
                'title'      => $titleSilver,
                'titleLevel' => [ 2, 3 ],
            ],
            [
                'level'      => 11,
                'xpBase'     => [ 26000, 30000 ],
                'icon'       => $iconSilver,
                'title'      => $titleSilver,
                'titleLevel' => [ 3, 3 ],
            ],
            // Gold
            [
                'level'      => 12,
                'xpBase'     => [ 30000, 32000 ],
                'icon'       => $iconGold,
                'title'      => $titleGold,
                'titleLevel' => [ 1, 4 ],
            ],
            [
                'level'      => 13,
                'xpBase'     => [ 32000, 34000 ],
                'icon'       => $iconGold,
                'title'      => $titleGold,
                'titleLevel' => [ 2, 4 ],
            ],
            [
                'level'      => 14,
                'xpBase'     => [ 34000, 36000 ],
                'icon'       => $iconGold,
                'title'      => $titleGold,
                'titleLevel' => [ 3, 4 ],
            ],
            [
                'level'      => 15,
                'xpBase'     => [ 36000, 40000 ],
                'icon'       => $iconGold,
                'title'      => $titleGold,
                'titleLevel' => [ 4, 4 ],
            ],
            // Diamond
            [
                'level'      => 16,
                'xpBase'     => [ 40000, 44000 ],
                'icon'       => $iconDiamond,
                'title'      => $titleDiamond,
                'titleLevel' => [ 1, 4 ],
            ],
            [
                'level'      => 17,
                'xpBase'     => [ 44000, 48000 ],
                'icon'       => $iconDiamond,
                'title'      => $titleDiamond,
                'titleLevel' => [ 2, 4 ],
            ],
            [
                'level'      => 18,
                'xpBase'     => [ 48000, 52000 ],
                'icon'       => $iconDiamond,
                'title'      => $titleDiamond,
                'titleLevel' => [ 3, 4 ],
            ],
            [
                'level'      => 19,
                'xpBase'     => [ 52000, 60000 ],
                'icon'       => $iconDiamond,
                'title'      => $titleDiamond,
                'titleLevel' => [ 4, 4 ],
            ],
            // Patron
            [
                'level'      => 20,
                'xpBase'     => [ 60000, 70000 ],
                'icon'       => trans('Ranking.iconPatron'),
                'title'      => trans('Ranking.titlePatron'),
                'titleLevel' => [ 1, 5 ],
            ],
            [
                'level'      => 21,
                'xpBase'     => [ 70000, 80000 ],
                'icon'       => trans('Ranking.iconPatron'),
                'title'      => trans('Ranking.titlePatron'),
                'titleLevel' => [ 2, 5 ],
            ],
            [
                'level'      => 22,
                'xpBase'     => [ 80000, 90000 ],
                'icon'       => trans('Ranking.iconPatron'),
                'title'      => trans('Ranking.titlePatron'),
                'titleLevel' => [ 3, 5 ],
            ],
            [
                'level'      => 23,
                'xpBase'     => [ 90000, 100000 ],
                'icon'       => trans('Ranking.iconPatron'),
                'title'      => trans('Ranking.titlePatron'),
                'titleLevel' => [ 4, 5 ],
            ],
            [
                'level'      => 24,
                'xpBase'     => [ 100000, PHP_INT_MAX ],
                'icon'       => trans('Ranking.iconPatron'),
                'title'      => trans('Ranking.titlePatron'),
                'titleLevel' => [ 5, 5 ],
            ],
        ]);
    }

    /**
     * Returns the Player Level instance.
     * @return PlayerLevel
     */
    public function getLevel(): PlayerLevel
    {
        /** @var PlayerLevel $playerLevel */
        $experience  = (int) $this->player_experience;
        $playerLevel = null;

        foreach (static::getLevels() as $level) {
            if ($experience >= $level['xpBase'][0] &&
                $experience < $level['xpBase'][1]) {
                $playerLevel = new PlayerLevel($level);
                break;
            }
        }

        $playerLevel->ranking = $this;

        return $playerLevel;
    }

    /**
     * Get User related to this ranking.
     */
    public function user(): User
    {
        return (new User)->find($this->user_id);
    }
}
