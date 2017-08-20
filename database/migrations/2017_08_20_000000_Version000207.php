<?php

declare(strict_types = 1);

use Application\Models\GridSubscription;
use Illuminate\Database\Migrations\Migration;

class Version000207 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        $gridSubscriptionsTable = '`' . DB::getTablePrefix() . (new GridSubscription)->getTable() . '`';

        DB::statement("
            ALTER TABLE {$gridSubscriptionsTable}
                CHANGE COLUMN `subscription_position` `subscription_position`
                    ENUM('" . GridSubscription::POSITION_TITULAR . "',
                         '" . GridSubscription::POSITION_TITULAR_RESERVE . "',
                         '" . GridSubscription::POSITION_RESERVE . "')
                    NOT NULL
                    DEFAULT '" . GridSubscription::POSITION_TITULAR . "';
        ");
    }
}
