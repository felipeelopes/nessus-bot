<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;

class Version000303 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::dropIfExists('events');
    }
}
