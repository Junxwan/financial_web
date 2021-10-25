<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNewsKeyWordsTable extends Migration
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
            $table->unsignedBigInteger('fund_id')->comment('funds.id');
            $table->string('keys')->comment('關鍵字');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '投信明細'");
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
