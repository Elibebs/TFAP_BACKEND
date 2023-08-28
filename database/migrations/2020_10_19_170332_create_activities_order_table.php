<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities.order', function (Blueprint $table) {
            $table->integer('id', true);
			$table->integer('shipping_id');
			$table->integer('payment_id');
			$table->string('reference_number', 191);
			$table->string('UID', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities.order');
    }
}
