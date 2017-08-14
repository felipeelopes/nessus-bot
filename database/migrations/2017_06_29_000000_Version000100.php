<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000100 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_number');
            $table->string('user_username', 32)
                ->offsetSet('nullable', true);
            $table->string('user_firstname', 240);
            $table->string('user_lastname', 240);
            $table->string('user_language', 5);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_number');
            $table->index('user_username');
            $table->index('user_language');
        });

        Schema::create('user_gamertags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('gamertag_value', 20);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }
}
