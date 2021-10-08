<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRationToProfitsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'profits';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->float('gross_ratio')->after('gross')->default(0)->comment('毛利率');
            $table->float('fee_ratio')->after('fee')->default(0)->comment('營業費用率');
            $table->float('profit_ratio')->after('profit')->default(0)->comment('利益率');
            $table->float('profit_pre_ratio')->after('profit_pre')->default(0)->comment('稅前淨利率');
            $table->float('profit_after_ratio')->after('profit_after')->default(0)->comment('稅後淨利率');
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
            $table->dropColumn('revenue_yoy');
            $table->dropColumn('gross_ratio');
            $table->dropColumn('fee_ratio');
            $table->dropColumn('profit_ratio');
            $table->dropColumn('profit_pre_ratio');
            $table->dropColumn('profit_after_ratio');
        });
    }
}
