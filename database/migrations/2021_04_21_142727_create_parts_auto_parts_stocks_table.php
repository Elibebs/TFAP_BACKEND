<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartsAutoPartsStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts.auto_part_stocks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('part_id');
            $table->integer('system_user_id');
            $table->string('quantity',191);
            $table->datetimetz('created_at');
            $table->datetimetz('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts.auto_part_stocks');
    }
}
