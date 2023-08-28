<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsShippingInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities.shipping_info', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('region')->nullable();
            $table->string('town')->nullable();
            $table->string('additional_info')->nullable();
            $table->boolean('active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities.shipping_info', function (Blueprint $table) {
            //
        });
    }
}
