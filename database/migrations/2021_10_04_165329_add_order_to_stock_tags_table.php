<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderToStockTagsTable extends Migration
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
        Schema::table($this->table, function (Blueprint $table) {
            $table->boolean('isGroup')->after('tag_id')->default(false)->comment('是否為組群');
            $table->tinyInteger('order')->after('isGroup')->default(0)->comment('順序');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('conversion_stock');
        });
    }
}
