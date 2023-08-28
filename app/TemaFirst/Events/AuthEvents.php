<?php

namespace App\TemaFirst\Events;

use App\Models\User;
use App\Models\SystemUser;
use App\Models\Category;
use App\Models\Role;
use App\Models\Verification;
use App\TemaFirst\Services\Sms;
use App\TemaFirst\Services\OneSignal;
use App\TemaFirst\Services\Twilio;
use App\TemaFirst\Utilities\Constants;
use Illuminate\Support\Facades\Log;

class AuthEvents
{
	public static function userHasLoggedIn(User $user)
	{

	}

	public static function userHasRegistered(User $user)
	{

	}

	// public static function userPasswordChanged(User $user)
	// {

	// }

	public static function userVerificationInstanceCreated(Verification $verification, User $user=null, $userBackup=false)
	{
		Log::notice("Verification pin created. Sending to user for phone number validation...");
		$message = "<#> Welcome to ".config('app.name').". Your verification code is : {$verification->verification_pin}. \nvrKjr4sh1ab";
		Log::notice($message);

		if(config("app.env") === Constants::ENV_PRODUCTION)
		{
			Twilio::sendSMS($verification->to_phone_number, $message);
		}
	}

	public static function userPhoneNumberChanged($phone_number, $pin)
	{
		Log::notice("Verification pin created. Sending to user for phone number validation...");
		$message = "<#> Account verification code is : {$pin}. \nvrKjr4sh1ab";
		Log::notice($message);

		if(config("app.env") === Constants::ENV_PRODUCTION)
		{
			Twilio::sendSMS($phone_number, $message);
		}
	}

	public static function userPinVerified(User $user)
	{
		Log::notice("User {$user->phone_number} verified successfully");

	}

	// public static function userForgotPasswordPinCreated(User $user)
	// {
	// 	Log::notice("User {$user->phone_number} forgot password pin created successfully");
	// }

	// public static function userForgotPasswordPinVerified(User $user)
	// {
	// 	Log::notice("User {$user->phone_number} forgot password pin verified successfully");
	// }

	public static function systemUserResetPassword(SystemUser $systemuser)
	{
		Log::notice("SystemUser {$systemuser->email} password reset successfully");
	}

	public static function systemUserRoleHasAdded(Role $role)
	{

	}

	public static function systemUserRoleHasDeleted(Role $role)
	{

	}


	public static function systemUserHasAdded(SystemUser $systemuser)
	{

	}
	public static function CategoryHasAdded(Category $category)
	{

	}
	public static function SubCategoryHasAdded(SubCategory $subcategory)
	{

	}


	public static function systemUserPasswordChanged(SystemUser $systemuser)
	{

	}

	// public static function workerForgotPasswordPinCreated(Worker $worker)
	// {
	// 	Log::notice("Worker {$worker->phone_number} forgot password pin created successfully");
	// }

	// public static function workerForgotPasswordPinVerified(Worker $worker)
	// {
	// 	Log::notice("Worker {$worker->phone_number} forgot password pin verified successfully");
	// }

	// public static function workerResetPassword(Worker $worker)
	// {
	// 	Log::notice("Worker {$worker->phone_number} password reset successfully");
	// }
}
