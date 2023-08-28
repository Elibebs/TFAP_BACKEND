<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesSearchHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities.search_history', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('keyword');
            $table->integer('customer_id')->nullable();
            $table->string('device_id');
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
        Schema::dropIfExists('activities.search_history');
    }
}
