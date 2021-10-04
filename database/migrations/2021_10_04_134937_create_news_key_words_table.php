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
    private $table = 'news_key_words';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('名稱');
            $table->string('keys')->comment('關鍵字');
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '新聞關鍵字'");
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
