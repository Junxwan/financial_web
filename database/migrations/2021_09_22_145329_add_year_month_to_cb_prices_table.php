<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddYearMonthToCbPricesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'cb_prices';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->year('year')->after('cb_id')->comment('年');
            $table->tinyInteger('month')->after('year')->comment('月');
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
            $table->dropColumn('year');
            $table->dropColumn('month');
        });    }
}
