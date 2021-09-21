<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbBalancesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'cb_balances';

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
            $table->year('year')->comment('年');
            $table->tinyInteger('month')->comment('月');
            $table->integer('change')->default(0)->comment('變動張數');
            $table->integer('balance')->default(0)->comment('餘額張數');
            $table->integer('change_stock')->default(0)->comment('變動轉換股數');
            $table->integer('balance_stock')->default(0)->comment('變動轉換餘額股數');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('cb_id')->references('id')->on('cbs');

            $table->unique(['cb_id', 'year', 'month']);
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '可轉債轉換餘額'");
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
