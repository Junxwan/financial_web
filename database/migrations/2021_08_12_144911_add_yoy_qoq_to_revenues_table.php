<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddYoyQoqToRevenuesTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'revenues';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->float('yoy', 10)->after('value')->default(0);
            $table->float('qoq', 10)->after('yoy')->default(0);
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
            $table->dropColumn('yoy');
            $table->dropColumn('qoq');
        });
    }
}
