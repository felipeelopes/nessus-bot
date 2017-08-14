<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000201 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('grids', function (Blueprint $table) {
            $gridDuration = $table->time('grid_duration');
            $gridDuration->offsetSet('after', 'grid_timing');
            $gridDuration->offsetSet('nullable', true);
        });
    }
}
