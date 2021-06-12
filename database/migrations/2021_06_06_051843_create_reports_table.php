<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id')->comment('stock.id');
            $table->date('date')->comment('日期');
            $table->string('title')->comment('標題');
            $table->tinyInteger('action')->comment('1:多,0:空');
            $table->float('market_eps_f')->default(0)->comment('市場財測');
            $table->tinyInteger('quarterly')->comment('季度');
            $table->tinyInteger('month')->comment('月份');
            $table->tinyInteger('pe')->default(0)->comment('pe');
            $table->tinyInteger('evaluate')->default(0)->comment('1:預期,2:預估');
            $table->float('value')->default(0)->comment('淨值');
            $table->float('price_f')->default(0)->comment('預估股價');
            $table->float('eps_1')->default(0)->comment('Q1 eps');
            $table->float('eps_2')->default(0)->comment('Q2 eps');
            $table->float('eps_3')->default(0)->comment('Q3 eps');
            $table->float('eps_4')->default(0)->comment('Q4 eps');
            $table->integer('revenue_month_1')->default(0)->comment('1月營收');
            $table->integer('revenue_month_2')->default(0)->comment('2月營收');
            $table->integer('revenue_month_3')->default(0)->comment('3月營收');
            $table->integer('revenue_month_4')->default(0)->comment('4月營收');
            $table->integer('revenue_month_5')->default(0)->comment('5月營收');
            $table->integer('revenue_month_6')->default(0)->comment('6月營收');
            $table->integer('revenue_month_7')->default(0)->comment('7月營收');
            $table->integer('revenue_month_8')->default(0)->comment('8月營收');
            $table->integer('revenue_month_9')->default(0)->comment('9月營收');
            $table->integer('revenue_month_10')->default(0)->comment('10月營收');
            $table->integer('revenue_month_11')->default(0)->comment('11月營收');
            $table->integer('revenue_month_12')->default(0)->comment('12月營收');
            $table->integer('revenue_1')->default(0)->comment('Q1營收');
            $table->integer('revenue_2')->default(0)->comment('Q2營收');
            $table->integer('revenue_3')->default(0)->comment('Q3營收');
            $table->integer('revenue_4')->default(0)->comment('Q4營收');
            $table->integer('gross_1')->default(0)->comment('Q1毛利');
            $table->integer('gross_2')->default(0)->comment('Q2毛利');
            $table->integer('gross_3')->default(0)->comment('Q3毛利');
            $table->integer('gross_4')->default(0)->comment('Q4毛利');
            $table->integer('fee_1')->default(0)->comment('Q1費用');
            $table->integer('fee_2')->default(0)->comment('Q2費用');
            $table->integer('fee_3')->default(0)->comment('Q3費用');
            $table->integer('fee_4')->default(0)->comment('Q4費用');
            $table->integer('outside_1')->default(0)->comment('Q1業外');
            $table->integer('outside_2')->default(0)->comment('Q2業外');
            $table->integer('outside_3')->default(0)->comment('Q3業外');
            $table->integer('outside_4')->default(0)->comment('Q4業外');
            $table->integer('other_1')->default(0)->comment('Q1其他');
            $table->integer('other_2')->default(0)->comment('Q2其他');
            $table->integer('other_3')->default(0)->comment('Q3其他');
            $table->integer('other_4')->default(0)->comment('Q4其他');
            $table->integer('tax_1')->default(0)->comment('Q1所得稅');
            $table->integer('tax_2')->default(0)->comment('Q2所得稅');
            $table->integer('tax_3')->default(0)->comment('Q3所得稅');
            $table->integer('tax_4')->default(0)->comment('Q4所得稅');
            $table->integer('profit_non_1')->default(0)->comment('Q1非控制權益');
            $table->integer('profit_non_2')->default(0)->comment('Q2非控制權益');
            $table->integer('profit_non_3')->default(0)->comment('Q3非控制權益');
            $table->integer('profit_non_4')->default(0)->comment('Q4非控制權益');
            $table->integer('profit_1')->default(0)->comment('Q1利益');
            $table->integer('profit_2')->default(0)->comment('Q2利益');
            $table->integer('profit_3')->default(0)->comment('Q3利益');
            $table->integer('profit_4')->default(0)->comment('Q4利益');
            $table->integer('profit_pre_1')->default(0)->comment('Q1稅前');
            $table->integer('profit_pre_2')->default(0)->comment('Q2稅前');
            $table->integer('profit_pre_3')->default(0)->comment('Q3稅前');
            $table->integer('profit_pre_4')->default(0)->comment('Q4稅前');
            $table->integer('profit_after_1')->default(0)->comment('Q1稅後');
            $table->integer('profit_after_2')->default(0)->comment('Q2稅後');
            $table->integer('profit_after_3')->default(0)->comment('Q3稅後');
            $table->integer('profit_after_4')->default(0)->comment('Q4稅後');
            $table->integer('profit_main_1')->default(0)->comment('Q1母權益');
            $table->integer('profit_main_2')->default(0)->comment('Q2母權益');
            $table->integer('profit_main_3')->default(0)->comment('Q3母權益');
            $table->integer('profit_main_4')->default(0)->comment('Q4母權益');
            $table->text('desc')->comment('說明')->nullable();
            $table->text('desc_total')->comment('總結')->nullable();
            $table->text('desc_revenue')->comment('營收說明')->nullable();
            $table->text('desc_gross')->comment('毛利說明')->nullable();
            $table->text('desc_fee')->comment('費用說明')->nullable();
            $table->text('desc_outside')->comment('業外說明')->nullable();
            $table->text('desc_other')->comment('其他收益說明')->nullable();
            $table->text('desc_tax')->comment('所得稅說明')->nullable();
            $table->text('desc_non')->comment('非控制權益說明')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('stock_id')->references('id')->on('stocks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
