<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCashsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'cashs';

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
            $table->year('year')->comment('年');
            $table->tinyInteger('quarterly')->comment('季度');
            $table->bigInteger('profit_pre')->comment('本期稅前淨利（淨損）');
            $table->bigInteger('depreciation')->comment('折舊費用');
            $table->bigInteger('business_activity')->comment('營業活動之淨現金流入（流出）');
            $table->bigInteger('real_estate')->comment('取得不動產、廠房及設備');
            $table->bigInteger('investment_activity')->comment('投資活動之淨現金流入（流出）');
            $table->bigInteger('fundraising_activity')->comment('籌資活動之淨現金流入（流出）');
            $table->bigInteger('cash_add')->comment('本期現金及約當現金增加（減少）數');
            $table->bigInteger('start_cash_balance')->comment('期初現金及約當現金餘額');
            $table->bigInteger('end_cash_balance')->comment('期末現金及約當現金餘額');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '現金流量表'");
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
