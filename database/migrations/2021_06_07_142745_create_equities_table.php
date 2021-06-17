<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEquitiesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'equities';

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
            $table->bigInteger('start_stock')->comment('股本合計-期初餘額');
            $table->bigInteger('end_stock')->comment('股本合計-期末餘額');
            $table->bigInteger('start_capital_reserve')->comment('資本公積-期初餘額');
            $table->bigInteger('end_capital_reserve')->comment('資本公積-期末餘額');
            $table->bigInteger('start_surplus_reserve')->comment('法定盈餘公積-期初餘額');
            $table->bigInteger('end_surplus_reserve')->comment('法定盈餘公積-期末餘額');
            $table->bigInteger('start_undistributed_surplus')->comment('未分配盈餘（或待彌補虧損）-期初餘額');
            $table->bigInteger('end_undistributed_surplus')->comment('未分配盈餘（或待彌補虧損）-期末餘額');
            $table->bigInteger('start_equity')->comment('權益總額-期初餘額');
            $table->bigInteger('end_equity')->comment('權益總額-期末餘額');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '權益變動表'");
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
