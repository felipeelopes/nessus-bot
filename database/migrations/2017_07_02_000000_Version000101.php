<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000101 extends Migration
{
    /**
     * Reverse the migrations.
     * @return void
     */
    public function down(): void
    {
        Schema::table('user_gamertags', function (Blueprint $table) {
            $table->dropColumn('gamertag_id');
        });
    }

    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('user_gamertags', function (Blueprint $table) {
            $gamertagId = $table->unsignedBigInteger('gamertag_id');
            $gamertagId->offsetSet('after', 'user_id');
            $gamertagId->offsetSet('nullable', true);
        });
    }
}
