<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'cbs';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id')->comment('stocks.id');
            $table->string('code', 10)->comment('代碼')->unique();
            $table->string('name', 10)->comment('名稱');
            $table->tinyInteger('period')->comment('期數');
            $table->date('start_date')->comment('發行時間');
            $table->date('end_date')->comment('到期時間');
            $table->tinyInteger('active_year')->comment('年限');
            $table->unsignedBigInteger('apply_total_amount')->comment('申請總額');
            $table->unsignedBigInteger('publish_total_amount')->comment('發佈總額');
            $table->float('publish_price')->default(0)->comment('發行價格');
            $table->float('conversion_price')->default(0)->comment('轉換價格');
            $table->date('start_conversion_date')->comment('開始轉換日期');
            $table->date('end_conversion_date')->comment('結束轉換日期');
            $table->float('conversion_premium_rate')->default(100)->comment('轉換溢價率');
            $table->float('coupon_rate')->default(0)->comment('票面利率');
            $table->tinyInteger('market')->default(0)->comment('1:上市,2:上櫃,3:興櫃,4:指數');
            $table->boolean('is_collateral')->default(false)->comment('是否有擔保');
            $table->string('url')->comment('公開資訊觀測站網址');

            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '可轉債'");
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
