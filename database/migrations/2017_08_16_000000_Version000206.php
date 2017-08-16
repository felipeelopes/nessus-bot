<?php

declare(strict_types = 1);

use Application\Models\Grid;
use Illuminate\Database\Migrations\Migration;

class Version000206 extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        $gridTable = '`' . DB::getTablePrefix() . (new Grid)->getTable() . '`';

        DB::statement("
            ALTER TABLE {$gridTable}
                CHANGE COLUMN `grid_status` `grid_status` 
                    ENUM('" . Grid::STATUS_WAITING . "', 
                         '" . Grid::STATUS_GATHERING . "', 
                         '" . Grid::STATUS_PLAYING . "', 
                         '" . Grid::STATUS_FINISHED . "', 
                         '" . Grid::STATUS_CANCELED . "', 
                         '" . Grid::STATUS_UNREPORTED . "') 
                    NOT NULL
                    DEFAULT '" . Grid::STATUS_WAITING . "';
        ");
    }
}
