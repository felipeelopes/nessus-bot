<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000302 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('user_gamertags', function (Blueprint $table) {
            $bungieAccount = $table->unsignedBigInteger('bungie_account');
            $bungieAccount->offsetSet('default', null);
            $bungieAccount->offsetSet('nullable', true);
            $bungieAccount->offsetSet('after', 'gamertag_id');
            $bungieMembership = $table->unsignedBigInteger('bungie_membership');
            $bungieMembership->offsetSet('default', null);
            $bungieMembership->offsetSet('nullable', true);
            $bungieMembership->offsetSet('after', 'bungie_account');
        });
    }
}
