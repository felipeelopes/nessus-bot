<?php

declare(strict_types = 1);

use Application\Models\Grid;
use Application\Models\GridSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000200 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::create('grids', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gamertag_id');
            $table->string('grid_title', 80);
            $table->string('grid_subtitle', 20)
                ->offsetSet('nullable', true);
            $table->string('grid_requirements', 400)
                ->offsetSet('nullable', true);
            $table->unsignedSmallInteger('grid_players');
            $table->dateTime('grid_timing');
            $table->enum('grid_status', [
                Grid::STATUS_WAITING,
                Grid::STATUS_GATHERING,
                Grid::STATUS_PLAYING,
                Grid::STATUS_FINISHED,
                Grid::STATUS_CANCELED,
            ])->offsetSet('default', Grid::STATUS_WAITING);
            $table->string('grid_status_details', 40)
                ->offsetSet('nullable', true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('gamertag_id');
            $table->index('grid_timing');
            $table->index('grid_status');
        });

        Schema::create('grid_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grid_id');
            $table->unsignedInteger('gamertag_id');
            $table->string('subscription_description', 20)
                ->offsetSet('nullable', true);
            $table->enum('subscription_rule', [
                GridSubscription::RULE_OWNER,
                GridSubscription::RULE_MANAGER,
                'titular',
                'reserve',
            ])->offsetSet('default', 'reserve');
            $table->enum('reserve_type', [
                'wait',
                'top',
            ])->offsetSet('nullable', true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('grid_id');
            $table->index('gamertag_id');
        });
    }
}
