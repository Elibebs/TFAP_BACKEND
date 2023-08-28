<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnManufacturerNumberPartsAutoPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts.auto_parts', function($table) {
            $table->dropColumn('location_id');
            $table->dropColumn('manufacturer_number');
            $table->dropColumn('universal_part');
            $table->dropColumn('series');
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts.auto_parts', function($table) {
            $table->string('manufacturer_name');
            $table->string('series');
            $table->integer('location_id');
            $table->integer('universal_part');
        });
    }
}
