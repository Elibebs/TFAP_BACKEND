<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnPartNameToPartsAutoPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts.auto_parts', function (Blueprint $table) {
            $table->dropColumn('part_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts.auto_parts', function (Blueprint $table) {
            $table->string('part_name');
        });
    }
}
