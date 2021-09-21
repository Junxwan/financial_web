<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbPricesTable extends Migration
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
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cb_id')->comment('cbs.id');
            $table->date('date')->default(0)->comment('日期');
            $table->float('open')->default(0)->comment('開盤價');
            $table->float('close')->default(0)->comment('收盤價');
            $table->float('high')->default(0)->comment('最高價');
            $table->float('low')->default(0)->comment('最低價');
            $table->float('increase')->default(0)->comment('漲跌幅');
            $table->float('amplitude')->default(0)->comment('振幅');
            $table->unsignedBigInteger('volume')->default(0)->comment('成交量');
            $table->unsignedBigInteger('amount')->default(0)->comment('交易金額');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('cb_id')->references('id')->on('cbs');

            $table->unique(['cb_id', 'date']);
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '可轉債價格'");
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
