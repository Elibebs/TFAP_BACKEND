<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client.devices', function (Blueprint $table) {
            $table->integer('id', true);
			$table->string('identifier', 191);
            $table->string('player_id', 191);
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
        Schema::dropIfExists('client.devices');
    }
}
