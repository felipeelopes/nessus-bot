<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000204 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('grid_subscriptions', function (Blueprint $table) {
            $reservedAt = $table->timestamp('reserved_at');
            $reservedAt->offsetSet('nullable', true);
            $reservedAt->offsetSet('after', 'subscription_position');
        });
    }
}
