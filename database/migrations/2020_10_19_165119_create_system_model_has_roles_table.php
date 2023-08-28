<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemModelHasRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.model_has_roles', function (Blueprint $table) {
			$table->integer('role_id');
			$table->string('model_type', 191);
			$table->bigInteger('model_id');
			$table->index(['model_id','model_type']);
			$table->primary(['role_id','model_id','model_type'], 'model_has_roles_pkey');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system.model_has_roles');
    }
}
