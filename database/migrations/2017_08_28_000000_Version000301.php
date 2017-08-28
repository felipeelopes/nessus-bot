<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000301 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('grids', function (Blueprint $table) {
            $gridDuration = $table->timestamp('notified_at');
            $gridDuration->offsetSet('default', null);
            $gridDuration->offsetSet('nullable', true);
            $gridDuration->offsetSet('after', 'grid_status_details');
        });

        Schema::table('grid_subscriptions', function (Blueprint $table) {
            $gridDuration = $table->timestamp('confirmed_at');
            $gridDuration->offsetSet('default', null);
            $gridDuration->offsetSet('nullable', true);
            $gridDuration->offsetSet('after', 'subscription_position');
        });
    }
}
