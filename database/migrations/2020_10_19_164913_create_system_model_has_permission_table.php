<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemModelHasPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.model_has_permission', function (Blueprint $table) {
			$table->integer('permission_id');
			$table->string('model_type', 191);
			$table->bigInteger('model_id');
			$table->primary(['permission_id','model_id','model_type'], 'model_has_permissions_pkey');
			$table->index(['model_id','model_type']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system.model_has_permission');
    }
}
