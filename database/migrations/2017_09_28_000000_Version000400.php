<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000400 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('stat_name', 40);
            $table->unsignedDecimal('stat_value', 14, 4);
            $table->timestamps();

            $table->index('user_id');
            $table->index('stat_name');
        });
    }
}
