<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.users', function (Blueprint $table) {
            $table->integer('id', true);
			$table->string('name', 191)->nullable();
			$table->string('email', 191)->nullable()->index('users_email_idx');
			$table->string('phone_number', 191)->nullable();
			$table->string('password', 191)->nullable();
			$table->string('status', 191);
			$table->string('type', 191)->nullable();
			$table->string('access_token', 191)->nullable()->index('users_access_token_idx');
			$table->string('session_id', 191)->nullable()->index('users_session_id_idx');
			$table->datetimetz('session_id_time')->nullable();
			$table->datetimetz('last_logged_in')->nullable();
			$table->string('player_id', 191)->nullable();
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
        Schema::dropIfExists('system.users');
    }
}
