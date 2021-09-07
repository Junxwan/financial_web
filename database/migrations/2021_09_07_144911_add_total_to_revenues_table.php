<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTotalToRevenuesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'revenues';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->bigInteger('total')->after('qoq')->default(0)->comment('當月累積營收');
            $table->bigInteger('y_total')->after('total')->default(0)->comment('去年同期累積營收');
            $table->float('total_increase', 10)->after('total')->default(0)->comment('累積營收比較增減');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('yoy');
            $table->dropColumn('qoq');
        });
    }
}
