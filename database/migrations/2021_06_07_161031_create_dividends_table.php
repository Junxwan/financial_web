<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDividendsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'dividends';

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
            $table->float('cash')->comment('現金股利');
            $table->float('stock')->comment('股票股利');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('stock_id')->references('id')->on('stocks');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '股利'");
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
