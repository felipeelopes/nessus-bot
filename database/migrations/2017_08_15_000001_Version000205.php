<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000205 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('grids', function (Blueprint $table) {
            $table->dropColumn('gamertag_id');
        });
    }
}
