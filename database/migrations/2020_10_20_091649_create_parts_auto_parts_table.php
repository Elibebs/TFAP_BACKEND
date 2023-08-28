<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsAutoPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts.auto_parts', function (Blueprint $table) {
            $table->integer('id', true);
			$table->string('name');
            $table->text('description',255);
            $table->integer('subcategory_id');
            $table->integer('model_id');
            $table->integer('location_id');
            $table->integer('make_id');
            $table->float('unit_price');
            $table->string('manufacturer_number',255);
            $table->string('part_number',255);
            $table->integer('universal_part');
            $table->string('fit_note',191)->nullable();
            $table->string('series',191);


            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts.auto_parts');
    }
}
