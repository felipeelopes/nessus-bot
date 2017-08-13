<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000202 extends Migration
{
    /**
     * Reverse the migrations.
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $userFirstname = $table->string('user_firstname', 240);
            $userFirstname->offsetSet('nullable', false);
            $userFirstname->offsetSet('change', true);
            $userLastname = $table->string('user_lastname', 240);
            $userLastname->offsetSet('nullable', false);
            $userLastname->offsetSet('change', true);
        });
    }

    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $userFirstname = $table->string('user_firstname', 240);
            $userFirstname->offsetSet('nullable', true);
            $userFirstname->offsetSet('change', true);
            $userLastname = $table->string('user_lastname', 240);
            $userLastname->offsetSet('nullable', true);
            $userLastname->offsetSet('change', true);
        });
    }
}
