<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStockPopulationsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'stock_populations';

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
            $table->unsignedBigInteger('population_id')->comment('populations.id');

            $table->foreign('stock_id')->references('id')->on('stocks');
            $table->foreign('population_id')->references('id')->on('populations');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '個股族群'");
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
