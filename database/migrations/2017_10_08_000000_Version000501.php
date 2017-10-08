<?php

declare(strict_types = 1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000501 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $bungieAccount = $table->boolean('activity_validated');
            $bungieAccount->offsetSet('default', false);
            $bungieAccount->offsetSet('after', 'activity_mode');
        });

        DB::select('
            UPDATE `nbot_activities`
            SET    `nbot_activities`.`activity_validated` = 1
            WHERE  `nbot_activities`.`created_at` <= ?
        ', [ Carbon::today()->subDay() ]);
    }
}
