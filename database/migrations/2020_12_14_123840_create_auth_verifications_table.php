<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthVerificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth.verifications', function(Blueprint $table)
		{
			$table->integer('verification_id', true);
			$table->integer('user_id')->index('verifications_user_id_idx');
			$table->string('verification_pin', 191);
			$table->boolean('is_active')->index('verifications_is_active_idx');
			$table->boolean('has_been_validated');
			$table->string('to_phone_number', 191);
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
		Schema::drop('auth.verifications');
	}

}
