<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFundStocksTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'fund_stocks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fund_id')->comment('funds.id');
            $table->unsignedBigInteger('stock_id')->comment('stocks.id');
            $table->year('year')->comment('年');
            $table->tinyInteger('month')->comment('月');
            $table->unsignedBigInteger('amount')->comment('持股金額');
            $table->float('ratio')->comment('持股比例');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('fund_id')->references('id')->on('funds');
            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '投信持股'");
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
