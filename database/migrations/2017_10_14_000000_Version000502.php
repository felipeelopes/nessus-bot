<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000502 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('user_gamertags', function (Blueprint $table) {
            $bungieClan = $table->unsignedBigInteger('bungie_clan');
            $bungieClan->offsetSet('default', null);
            $bungieClan->offsetSet('nullable', true);
            $bungieClan->offsetSet('after', 'bungie_membership');
        });
    }
}
