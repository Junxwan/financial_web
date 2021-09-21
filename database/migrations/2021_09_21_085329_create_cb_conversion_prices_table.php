<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbConversionPricesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'cb_conversion_prices';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cb_id')->comment('cbs.id');
            $table->float('value')->default(0)->comment('轉換價格');
            $table->integer('stock')->default(0)->comment('轉換股數');
            $table->tinyInteger('type')->comment('1:掛牌,2:反稀釋,3:重設,4:不重設,5:特別重設,6:不特別重設');
            $table->date('date')->comment('生效日期');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('cb_id')->references('id')->on('cbs');

            $table->unique(['cb_id', 'date']);
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '可轉債轉換價調整'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
