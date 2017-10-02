<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Ranking\PlayerRanking;
use Application\Services\Contracts\ServiceContract;
use Cache;
use DB;
use Illuminate\Support\Collection;

class UserExperienceService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): UserExperienceService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Forget global ranking data.
     */
    public function forgetGlobalRanking()
    {
        //@todo
        //Cache::forget(__CLASS__ . '@globalRanking');
    }

    /**
     * Returns the Global ranking.
     */
    public function getGlobalRanking(): Collection
    {
        $globalRanking = Cache::rememberForever(__CLASS__ . '@globalRanking', function () {
            return DB::select('
                SELECT
                    `nbot_users`.`id` AS `user_id`,
                    COUNT(*) AS `player_activities`,
                    CAST((
                        SELECT ROUND(SUM(`nbot_activities`.`value_duration`) / 3600, 1)
                        FROM `nbot_activities`
                        WHERE `nbot_activities`.`user_id` = `nbot_users`.`id`
                    ) AS DOUBLE) AS `player_timing`,
                    (
                        SELECT MAX(`nbot_activities`.`player_light`)
                        FROM `nbot_activities`
                        WHERE `nbot_activities`.`user_id` = `nbot_users`.`id`
                    ) AS `player_light`,
                    ROUND(
                        SUM(`experiences`.`activity_experience`) *
                        (
                            1 +
                            (
                                SELECT COUNT(DISTINCT `clan_members`.`user_id`)
                                FROM `nbot_activities` AS `clan_members`
                                
                                WHERE
                                    `clan_members`.`user_id` != `nbot_users`.`id` AND
                                    `clan_members`.`value_duration` >= 600 AND
                                    `clan_members`.`activity_instance` IN (
                                        SELECT `user_instances`.`activity_instance`
                                        FROM `nbot_activities` AS `user_instances`
                                        WHERE
                                            `user_instances`.`user_id` = `nbot_users`.`id` AND
                                            `user_instances`.`value_duration` >= 600
                                    )
                            ) * 0.025
                        ), 
                        2
                    ) AS `player_experience`,
                    (
                        SELECT COUNT(DISTINCT `clan_members`.`user_id`)
                        FROM `nbot_activities` AS `clan_members`
                        
                        WHERE
                            `clan_members`.`user_id` != `nbot_users`.`id` AND
                            `clan_members`.`value_duration` >= 180 AND
                            `clan_members`.`activity_instance` IN (
                                SELECT `user_instances`.`activity_instance`
                                FROM `nbot_activities` AS `user_instances`
                                WHERE
                                    `user_instances`.`user_id` = `nbot_users`.`id` AND
                                    `user_instances`.`value_duration` >= 180
                            )
                    ) AS `player_interation`,
                    DATEDIFF(NOW(), `nbot_users`.`created_at`) AS `player_register`
                FROM (
                    SELECT
                        `nbot_activities`.*,
                        (
                            (
                                SQRT(`nbot_activities`.`value_duration`) +
                                `nbot_activities`.`value_kills` * 0.25 +
                                `nbot_activities`.`value_precision` * 0.05 +
                                `nbot_activities`.`value_assists` * 0.05 +
                                GREATEST(0, ( `nbot_activities`.`value_duration` / 180 ) - `nbot_activities`.`value_deaths`) * 0.25
                            )
                            *
                            (
                                1 +
                                (
                                    SELECT LEAST(COUNT(*), 5)
                                    FROM `nbot_activities` AS `clan_members`
                                    WHERE
                                        `clan_members`.`activity_instance` = `nbot_activities`.`activity_instance` AND
                                        `clan_members`.`id` != `nbot_activities`.`id`
                                ) * 0.20 +
                                IF(
                                    `nbot_activities`.`player_light` IS NOT NULL,
                                    IFNULL((
                                        SELECT LEAST(
                                            100, 
                                            SUM(GREATEST(0, CAST(`nbot_activities`.`player_light` AS SIGNED) - CAST(`clan_members`.`player_light` AS SIGNED)))
                                        )
                                        FROM `nbot_activities` AS `clan_members`
                                        WHERE
                                            `clan_members`.`activity_instance` = `nbot_activities`.`activity_instance` AND
                                            `clan_members`.`player_light` IS NOT NULL AND
                                            `clan_members`.`id` != `nbot_activities`.`id`
                                    ), 0) * 0.01,
                                    0
                                )
                            )
                        ) AS `activity_experience`
                    FROM `nbot_activities`
                        
                    WHERE
                        (
                            SELECT TRUE
                            FROM `nbot_activities` AS `clan_members`
                            WHERE
                                `clan_members`.`activity_instance` = `nbot_activities`.`activity_instance` AND
                                `clan_members`.`id` != `nbot_activities`.`id`
                            LIMIT 1
                        )
                    
                    ORDER BY
                        `activity_experience` DESC
                ) AS `experiences`
                
                INNER JOIN `nbot_users` ON
                    `nbot_users`.`id` = `experiences`.`user_id`
                    
                WHERE `nbot_users`.`deleted_at` IS NULL
                GROUP BY `nbot_users`.`id`
                ORDER BY `player_experience` DESC
            ');
        });

        return (new Collection(array_map(function ($playerRanking) {
            return new PlayerRanking($playerRanking);
        }, $globalRanking)))->keyBy('user_id');
    }
}
