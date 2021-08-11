<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateNameToFundsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'funds';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->string('name', 50)->change();
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
