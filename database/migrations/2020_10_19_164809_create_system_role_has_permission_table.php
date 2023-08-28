<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemRoleHasPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.role_has_permission', function (Blueprint $table) {
			$table->integer('permission_id');
			$table->integer('role_id');
			$table->primary(['permission_id','role_id'], 'role_has_permissions_pkey');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system.role_has_permission');
    }
}
