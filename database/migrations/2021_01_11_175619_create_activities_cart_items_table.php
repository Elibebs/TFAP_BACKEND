<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities.cart_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('cart_id');
            $table->integer('part_id');
            $table->string('unit_price');
            $table->string('status');
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
        Schema::dropIfExists('activities.cart_items');
    }
}
