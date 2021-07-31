<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDayTradesToPricesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'prices';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->integer('day_trades_volume')->default(0)->after('foreign_value')->comment('現股當沖量');
            $table->bigInteger('day_tradeB_value')->default(0)->after('day_trades_volume')->comment('股當沖買進金額');
            $table->bigInteger('day_tradeS_value')->default(0)->after('day_tradeB_value')->comment('股當沖買進金額');
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
            $table->dropColumn('normal_day_trades_volume');
            $table->dropColumn('day_tradeB_value');
            $table->dropColumn('day_tradeS_value');
        });
    }
}
