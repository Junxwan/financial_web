<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProfitsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'profits';

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
            $table->bigInteger('revenue')->comment('營業收入合計');
            $table->bigInteger('cost')->comment('營業成本合計');
            $table->bigInteger('gross')->comment('營業毛利（毛損）');
            $table->bigInteger('market')->comment('推銷費用');
            $table->bigInteger('management')->comment('管理費用');
            $table->bigInteger('research')->comment('研究發展費用');
            $table->bigInteger('fee')->comment('營業費用合計');
            $table->bigInteger('profit')->comment('營業利益（損失）');
            $table->bigInteger('outside')->comment('營業外收入及支出合計');
            $table->bigInteger('other')->comment('其他收益及費損淨額');
            $table->bigInteger('profit_pre')->comment('稅前淨利（淨損）');
            $table->bigInteger('profit_after')->comment('本期淨利（淨損）');
            $table->bigInteger('profit_total')->comment('本期綜合損益總額');
            $table->bigInteger('profit_main')->comment('母公司業主（淨利∕損）');
            $table->bigInteger('profit_non')->comment('非控制權益（淨利∕損）');
            $table->bigInteger('profit_main_total')->comment('母公司業主（綜合損益）');
            $table->bigInteger('profit_non_total')->comment('非控制權益（綜合損益）');
            $table->bigInteger('tax')->comment('所得稅費用（利益）合計');
            $table->float('eps')->comment('基本每股盈餘');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '綜合損益表'");
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
