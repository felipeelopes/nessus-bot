<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000201 extends Migration
{
    /**
     * Reverse the migrations.
     * @return void
     */
    public function down(): void
    {
        Schema::table('grids', function (Blueprint $table) {
            $table->dropColumn('grid_duration');
        });
    }

    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('grids', function (Blueprint $table) {
            $gridDuration = $table->time('grid_duration');
            $gridDuration->offsetSet('after', 'grid_timing');
        });
    }
}
