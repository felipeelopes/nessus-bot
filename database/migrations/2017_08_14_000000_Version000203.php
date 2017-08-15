<?php

declare(strict_types = 1);

use Application\Models\GridSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Version000203 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        $gridSubscriptionsTable = '`' . DB::getTablePrefix() . (new GridSubscription)->getTable() . '`';

        // Split `subscription_rule` in two:
        // `subscription_position` to store the user position on grid: titular, reserve-top or reserve-bottom;
        // `subscription_rule` to store the user rule on grid: owner, manager or user.
        DB::statement("
            ALTER TABLE {$gridSubscriptionsTable}
                ADD COLUMN `subscription_position` 
                    ENUM('" . GridSubscription::POSITION_TITULAR . "', 
                         '" . GridSubscription::POSITION_RESERVE_TOP . "', 
                         '" . GridSubscription::POSITION_RESERVE_BOTTOM . "') 
                    NOT NULL 
                    DEFAULT '" . GridSubscription::POSITION_TITULAR . "' 
                    AFTER `subscription_rule`;
        ");

        DB::statement("
            ALTER TABLE {$gridSubscriptionsTable}
                CHANGE COLUMN `subscription_rule` `subscription_rule` 
                    ENUM('" . GridSubscription::RULE_OWNER . "',
                        '" . GridSubscription::RULE_MANAGER . "',
                        '" . GridSubscription::RULE_USER . "') 
                    NOT NULL 
                    DEFAULT '" . GridSubscription::RULE_USER . "' 
                    AFTER `subscription_description`;
        ");

        // With that, `reserve_type` could be dropped.
        Schema::table('grid_subscriptions', function (Blueprint $table) {
            $table->dropColumn('reserve_type');
        });
    }
}
