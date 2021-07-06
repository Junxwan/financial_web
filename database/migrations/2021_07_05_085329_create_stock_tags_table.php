<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStockTagsTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'stock_tags';

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
            $table->unsignedBigInteger('tag_id')->comment('tags.id');

            $table->foreign('stock_id')->references('id')->on('stocks');
            $table->foreign('tag_id')->references('id')->on('tags');

            $table->unique(['stock_id', 'tag_id']);
        });

        DB::statement("ALTER TABLE `{$this->table}` COMMENT = '個股標籤'");
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
