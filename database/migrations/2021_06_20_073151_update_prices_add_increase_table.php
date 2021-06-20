<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePricesAddIncreaseTable extends Migration
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
            $table->float('increase_5')->default(0)->comment('週漲幅');
            $table->float('increase_23')->default(0)->comment('月漲幅');
            $table->float('increase_63')->default(0)->comment('季漲幅');
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
            $table->dropColumn('increase_5');
            $table->dropColumn('increase_23');
            $table->dropColumn('increase_63');
        });
    }
}
