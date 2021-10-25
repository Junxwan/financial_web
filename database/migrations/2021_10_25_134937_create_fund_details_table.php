<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFundDetailsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'fund_details';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->year('year')->comment('年');
            $table->tinyInteger('month')->comment('月');
            $table->unsignedBigInteger('fund_id')->comment('funds.id');
            $table->unsignedBigInteger('scale')->comment('基金規模(台幣)');
            $table->float('value')->comment('單位淨值(台幣)');
            $table->smallInteger('natural_person')->comment('自然人受益人數');
            $table->smallInteger('legal_person')->comment('法人受益人數');
            $table->smallInteger('person')->comment('總受益人數');
            $table->unsignedBigInteger('buy_amount')->comment('本月申購總金額(台幣)');
            $table->unsignedBigInteger('sell_amount')->comment('本月買回總金額(台幣)');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('建立時間');

            $table->unique(['fund_id', 'year', 'month']);
            $table->foreign('fund_id')->references('id')->on('funds');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '投信資料明細'");
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
