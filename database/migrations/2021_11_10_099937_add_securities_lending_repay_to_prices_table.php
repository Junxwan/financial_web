<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSecuritiesLendingRepayToPricesTable extends Migration
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
            $table->unsignedInteger('securities_lending_repay')->default(0)->after('foreign')->comment('融劵劵償');
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
            $table->dropColumn('securities_lending_repay');
        });
    }
}
