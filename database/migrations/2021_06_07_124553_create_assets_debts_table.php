<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAssetsDebtsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'assets_debts';

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
            $table->tinyInteger('season')->comment('季度');
            $table->bigInteger('cash')->comment('現金及約當現金');
            $table->bigInteger('stock')->comment('存貨');
            $table->bigInteger('bill_receivable')->comment('應收票據淨額');
            $table->bigInteger('receivable')->comment('應收帳款淨額');
            $table->bigInteger('receivable_person')->comment('應收帳款－關係人淨額');
            $table->bigInteger('receivable_other')->comment('其他應收款淨額');
            $table->bigInteger('receivable_other_person')->comment('其他應收款－關係人淨額');
            $table->bigInteger('equity_method')->comment('採用權益法之投資');
            $table->bigInteger('real_estate')->comment('不動產、廠房及設備');
            $table->bigInteger('intangible_assets')->comment('無形資產');
            $table->bigInteger('flow_assets_total')->comment('流動資產合計');
            $table->bigInteger('non_flow_assets_total')->comment('非流動資產合計');
            $table->bigInteger('assets_total')->comment('資產總額');
            $table->bigInteger('short_loan')->comment('短期借款');
            $table->bigInteger('bill_short_payable')->comment('應付短期票券');
            $table->bigInteger('payable')->comment('應付帳款');
            $table->bigInteger('payable_person')->comment('應付帳款－關係人');
            $table->bigInteger('payable_other')->comment('其他應付款');
            $table->bigInteger('payable_other_person')->comment('其他應付款項－關係人');
            $table->bigInteger('payable_company_debt')->comment('應付公司債');
            $table->bigInteger('tax_debt')->comment('遞延所得稅負債');
            $table->bigInteger('flow_debt_total')->comment('流動負債合計');
            $table->bigInteger('non_flow_debt_total')->comment('非流動負債合計');
            $table->bigInteger('debt_total')->comment('負債總額');
            $table->bigInteger('capital')->comment('股本合計');
            $table->bigInteger('main_equity_total')->comment('歸屬於母公司業主之權益合計');
            $table->bigInteger('non_equity_total')->comment('非控制權益');
            $table->bigInteger('equity_total')->comment('權益總額');
            $table->bigInteger('debt_equity_total')->comment('負債及權益總計');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '資產負債表'");
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
