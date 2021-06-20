<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePricesTable extends Migration
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
            $table->unsignedBigInteger('value')->default(0)->comment('成交金額');
            $table->integer('main')->default(0)->comment('主力買賣超');
            $table->integer('fund')->default(0)->comment('投信買賣超');
            $table->integer('foreign')->default(0)->comment('外資買賣超');
            $table->integer('volume_5')->default(0)->comment('5日均量');
            $table->integer('volume_10')->default(0)->comment('10日均量');
            $table->integer('volume_20')->default(0)->comment('20日均量');
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
            $table->dropColumn('value');
            $table->dropColumn('main');
            $table->dropColumn('fund');
            $table->dropColumn('foreign');
            $table->dropColumn('volume_5');
            $table->dropColumn('volume_10');
            $table->dropColumn('volume_20');
        });
    }
}
