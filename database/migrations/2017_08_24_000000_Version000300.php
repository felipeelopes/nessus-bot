<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000300 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $referenceType = $table->string('reference_type', 120);
            $referenceType->offsetSet('nullable', true);
            $referenceType->offsetSet('default', null);
            $referenceId = $table->unsignedInteger('reference_id');
            $referenceId->offsetSet('nullable', true);
            $referenceId->offsetSet('default', null);
            $table->string('event_executor', 120);
            $table->timestamp('event_execution');
            $eventExecuted = $table->timestamp('event_executed');
            $eventExecuted->offsetSet('nullable', true);
            $eventExecuted->offsetSet('default', null);
            $eventFailures = $table->unsignedTinyInteger('event_failures');
            $eventFailures->offsetSet('default', 0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('event_executor');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_type', 120);
            $referenceId = $table->unsignedInteger('reference_id');
            $referenceId->offsetSet('nullable', true);
            $referenceId->offsetSet('default', null);
            $table->string('setting_name', 40);
            $settingValue = $table->mediumText('setting_value');
            $settingValue->offsetSet('nullable', true);
            $settingValue->offsetSet('default', null);
            $table->timestamps();
            $table->softDeletes();

            $table->index([ 'reference_type', 'reference_id' ]);
            $table->index('setting_name');
        });
    }
}
