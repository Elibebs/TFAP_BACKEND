<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities.order_items', function (Blueprint $table) {
            $table->integer('id', true);
			$table->string('name');
            $table->integer('part_id');
            $table->integer('order_id');
            $table->float('unit_price');
			$table->string('quantity',191);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities.order_items');
    }
}
