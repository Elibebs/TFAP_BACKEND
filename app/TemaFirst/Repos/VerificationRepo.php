<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\Verification;
use App\Models\ForgotPassword;
use App\TemaFirst\Utilities\Generators;
use App\TemaFirst\Utilities\Constants;
use Illuminate\Support\Facades\Log;

class VerificationRepo extends BaseRepo
{
	public function createVerificationEntry(Array $data)
	{
		// Get and update existing verification for user
		$existingVerification = Verification::where("user_id", $data['user_id'])->where("is_active", true)->first();
		if($existingVerification)
		{
			Log::notice("using existing Verification {$data['user_id']} {$existingVerification->verification_pin}");
			$existingVerification->is_active = true;
			$existingVerification->has_been_validated = false;
			$existingVerification->updated_at = Carbon::now();
			$existingVerification->update();

			return $existingVerification;
		}

		// if updated, create new verification
		$verification = new Verification;

		$verification->user_id = $data['user_id'];
		$verification->verification_pin = Generators::generateVerificationPin();
		$verification->is_active = true;
		$verification->has_been_validated = false;
		$verification->to_phone_number = $data['phone_number'];
		$verification->created_at = Carbon::now();
		$verification->updated_at = Carbon::now();

		if($verification->save())
		{
			Log::notice("using new Verification {$verification->verification_pin}");
			return $verification;
		}

		return null;
	}

	public function attemptVerifyPin(String $pin, $userId)
	{

		$verification = Verification::where("user_id", $userId)->where("is_active", true)->first();
		Log::notice("attemptVerifyPin => {$pin}... ... {$verification} userId= {$userId}");
		if(!isset($verification))
		{
			return false;
		}

		Log::notice("attemptVerifyPin = {$verification->verification_pin} => {$pin}");
		if($verification->verification_pin !== $pin)
		{
			return false;
		}

		$verification->is_active = false;
		$verification->has_been_validated = true;
		$verification->updated_at = Carbon::now();

		return $verification->update();
	}

	public function userCreateForgotPasswordPinEntry(Array $data)
	{
		// Get and update existing verification for user
		$existingPinVerification = ForgotPassword::where("user_id", $data['user_id'])->where("is_active", true)->first();
		if($existingPinVerification)
		{
			Log::notice("updating existing pin... {$existingPinVerification->pin}");
			$existingPinVerification->is_active = false;
			$existingPinVerification->has_been_validated = false;
			$existingPinVerification->updated_at = Carbon::now();
			$existingPinVerification->update();
		}

		// if updated, create new verification
		$forgotPassword = new ForgotPassword;

		$forgotPassword->user_id = $data['user_id'];
		$forgotPassword->pin = Generators::generateVerificationPin();
		$forgotPassword->is_active = true;
		$forgotPassword->has_been_validated = false;
		$forgotPassword->to_phone_number = $data['phone_number'];
		$forgotPassword->created_at = Carbon::now();
		$forgotPassword->updated_at = Carbon::now();

		if($forgotPassword->save())
		{
			Log::notice("saving and returning new forgot password pin {$forgotPassword->pin}");
			return $forgotPassword;
		}

		return null;
	}

	public function userAttemptVerifyForgotPassword(String $pin, $userId)
	{
		Log::notice("attemptVerifyPin forgotPassword = {$pin}");
		$forgotPassword = ForgotPassword::where("user_id", $userId)->where("is_active", true)->first();
		if(!isset($forgotPassword))
		{
			return false;
		}

		Log::notice("attemptVerifyPin = {$forgotPassword->pin} => {$pin}");
		if($forgotPassword->pin !== $pin)
		{
			return false;
		}

		$forgotPassword->is_active = false;
		$forgotPassword->has_been_validated = true;
		$forgotPassword->updated_at = Carbon::now();

		return $forgotPassword->update();
	}

	public function hasSytemUserForgotPasswordPinBeenGeneratedRecently($systemUserId)
	{
		$forgotPasswordPin = ForgotPassword::where("id", $systemUserId)
			->orderBy("created_at", "DESC")->first();

		if(isset($forgotPasswordPin))
		{
			$timeDiff = time() - strtotime($forgotPasswordPin->created_at);
			if($timeDiff <= Constants::PASSWORD_RESET_SESSION_TIMOUT)
			{
				return true;
			}
		}

		return false;

	}

}
