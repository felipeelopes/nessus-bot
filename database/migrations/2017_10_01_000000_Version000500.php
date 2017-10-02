<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000500 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('activity_instance');
            $table->unsignedTinyInteger('activity_mode');
            $playerLight = $table->unsignedSmallInteger('player_light');
            $playerLight->offsetSet('nullable', true);
            $playerLight->offsetSet('default', null);
            $table->unsignedTinyInteger('value_completed');
            $table->unsignedSmallInteger('value_kills');
            $table->unsignedSmallInteger('value_assists');
            $table->unsignedSmallInteger('value_deaths');
            $table->unsignedSmallInteger('value_precision');
            $table->unsignedSmallInteger('value_duration');
            $table->timestamps();

            $table->index('user_id');
            $table->index('activity_instance');
            $table->index('activity_mode');
        });
    }
}
