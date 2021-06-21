<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePricesAddValueTable extends Migration
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
            $table->bigInteger('fund_value')->default(0)->comment('投信買賣超金額');
            $table->bigInteger('foreign_value')->default(0)->comment('外資買賣超金額');
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
            $table->dropColumn('fund_value');
            $table->dropColumn('foreign_value');
        });
    }
}
