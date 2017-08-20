<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000208 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $userLanguage = $table->string('user_language', 5);
            $userLanguage->offsetSet('nullable', true);
            $userLanguage->offsetSet('change', true);
        });
    }
}
