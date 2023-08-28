<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\SystemUserRepo;
use App\TemaFirst\Repos\UserRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\AuthTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Mail\WelcomeMail;
use App\Notifications\WelcomeUser;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\Models\SytstemUser;
use Twilio\Exceptions\RestException;


class AuthActivity extends BaseActivity
{
	use AuthTrait;

    protected $systemUserRepo;
	protected $verificationRepo;
	protected $userRepo;
	protected $apiResponse;

	public function __construct(
		SystemUserRepo $systemUserRepo,
		UserRepo $userRepo,
        VerificationRepo $verificationRepo,
		ApiResponse $apiResponse
	)
    {
		$this->systemUserRepo = $systemUserRepo;
		$this->userRepo = $userRepo;
        $this->verificationRepo = $verificationRepo;
		$this->apiResponse = $apiResponse;
	}
	


	public function freeLoginUser(Array $data){
    	// Validate request parameters
		$missingParams = Validator::validateRequiredParams($this->apiUserLoginParams, $data);
		if(!empty($missingParams))
		{
			$errors = Validator::convertToRequiredValidationErrors($missingParams);
			ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

			return $this->apiResponse->validationError(
				["errors" => $errors]
			);
		}
		// Attempt to login user and get appropriate response:
		// Response -> null if unauthorized, User object if authorized

		$userLogin = $this->userRepo->freeLogin($data);
		if($userLogin)
		{
			unset($userLogin['password']);
			$message = "User : {$data['phone_number']} successfully logged in";
			Log::notice($message);
			AuthEvents::userHasLoggedIn($userLogin);

			$verification = $this->verificationRepo->createVerificationEntry([
				'user_id' => $userLogin->user_id,
				'phone_number' => $userLogin->phone_number
			]);

			Log::notice($userLogin->phone_number);

			if($verification)
			{
				Log::notice("verification called ".$verification);
				try{
					AuthEvents::userVerificationInstanceCreated($verification, $userLogin);
				}catch(RestException $rest){
					Log::notice($rest);
				}
				
			}

			return $this->apiResponse->success($message, ["data" => $userLogin->toArray()] );
		}
		
		ErrorEvents::apiErrorOccurred("Unauthorized login by User {$data['phone_number']}", "warning");
		return $this->apiResponse->unauthorized();
	}

	public function userRegister(Array $data){
    	// Validate request parameters
		$missingParams = Validator::validateRequiredParams($this->apiUserRegisterParams, $data);
		if(!empty($missingParams))
		{
			$errors = Validator::convertToRequiredValidationErrors($missingParams);
			ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

			return $this->apiResponse->validationError(
				["errors" => $errors]
			);
		}

		//Validate phone number been used
		if($this->userRepo->getUserByPhoneNumber($data['phone_number'])){
			$message = "Phone number {$data['phone_number']} already in use";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->generalError($message);
		}

		//Validate email been used
		if($this->userRepo->getUserByEmail($data['email'])){
			$message = "Email {$data['email']} already in use";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->generalError($message);
		}

		//Check account type
		if($data['account_type'] !== "CUSTOMER" &&  $data['account_type'] !== "AUTO MECHANIC"){
			$message = "Account Type can be CUSTOMER or AUTO MECHANIC";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->generalError($message);
		}

		$registeredUser = $this->userRepo->registerFreeUser($data);
		if($registeredUser){
			unset($registeredUser['password']);
			$message = "User : {$data['phone_number']} registered successfully";
			Log::notice($message);
			AuthEvents::userHasRegistered($registeredUser);

			return $this->freeLoginUser($data);
		}else{
			$message = "Unable to complete registration for {$data['phone_number']}";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->generalError($message);
		}

		ErrorEvents::apiErrorOccurred("Unauthorized login by User {$data['phone_number']}", "warning");
		return $this->apiResponse->unauthorized();
	}


	public function verifyUserPin(Array $data, String $accessToken, String $sessionId)
    {
    	// Validate request parameters
		$missingParams = Validator::validateRequiredParams($this->apiVerificationPinParams, $data);
		if(!empty($missingParams))
		{
			$errors = Validator::convertToRequiredValidationErrors($missingParams);
			ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

			return $this->apiResponse->validationError(
				["errors" => $errors]
			);
		}

		// get user whose access token is provided. If no user return
		$user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);
		if(!isset($user))
		{
			$message = "User with supplied access-token/session-id not found";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->generalError($message);
		}

		// Check if verification pin is correct
		if($this->verificationRepo->attemptVerifyPin($data['pin'], $user->user_id))
		{
			if($this->userRepo->setUserVerified($user['user_id']))
			{
				$user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);
				$message = "User verified successfully";
				AuthEvents::userPinVerified($user);
				return $this->apiResponse->success($message, ["data" => $user] );
			}
		}
		else
		{
			$message = "Incorrect verification pin";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->generalError($message);
		}
	}
	
	public function getUserProfile($accessToken, $sessionId)
    {
    	$user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);
    	if($user)
    	{
    		unset($user['password']);
    		$message = "User profile obtained successfully";
    		Log::notice($message);
    		return $this->apiResponse->success($message, ['data' => $user->toArray()]);
    	}

    	$errMsg = "Could not obtain user profile";
    	ErrorEvents::apiErrorOccurred($errMsg);
		return $this->apiResponse->generalError($errMsg);
	}
	
	public function updateUserProfile(Array $data, $accessToken, $sessionId)
    {
		$user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);
		if(isset($data['phone_number']) && $data['phone_number']!=$user->phone_number){
			$existingUser = $this->userRepo->getWherePhoneNumber($data['phone_number']);

			if(!empty($existingUser))
			{
				$message = "The phone number already exist";
				ErrorEvents::apiErrorOccurred("Validation error, {$message}");

				return $this->apiResponse->generalError($message);
			}
		}

		//Valiate Email Address
		if(isset($data['email']) && $data['email'] != $user->email){
			//Checking for existing email address
			$existingEmailUser = $this->userRepo->getUserByEmail($data['email']);
			if(!empty($existingEmailUser)){
				$message = "Email address already exist";
				ErrorEvents::apiErrorOccurred("Validation error, {$message}");

				return $this->apiResponse->validateError($message);
			}

			//Check for valid email
			if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
				$message = "Invalid email address";
				ErrorEvents::apiErrorOccurred("Validation error, {$message}");

				return $this->apiResponse->generalError($message);
			}
		}


    	if($user)
    	{
    		if($this->userRepo->updateUserProfile($data, $user->user_id))
    		{
    			$updatedUserProfile = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);
					unset($updatedUserProfile['password']);

    			$message = "User profile updated successfully";
					Log::notice($message);

					if($updatedUserProfile->verified===false){
						Log::notice('User profile is not verified. verification pin incoming...');
						$this->userVerifyPin($data);
					}
	    		return $this->apiResponse->success($message, ['data' => $updatedUserProfile->toArray()]);
    		}
    	}

    	$errMsg = "Could not obtain user for update :(";
    	ErrorEvents::apiErrorOccurred($errMsg);
		return $this->apiResponse->generalError($errMsg);
	}

	public function userVerifyPin($data)
    {
    	$missingParams = Validator::validateRequiredParams(["phone_number"], $data);
		if(!empty($missingParams))
		{
			$errors = Validator::convertToRequiredValidationErrors($missingParams);
			ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

			return $this->apiResponse->validationError(
				["errors" => $errors]
			);
		}

    	$user = $this->userRepo->getUserByPhoneNumber($data['phone_number']);
    	if(!isset($user))
    	{
    		$message = "User with phone number {$data['phone_number']} not found";
			ErrorEvents::apiErrorOccurred($message);
			return $this->apiResponse->notFoundError($message);
    	}

    	$userPhone = $user->phone_number;
    	$pin = Generators::generateVerificationPin();

    	$verificationObject = $this->verificationRepo->createVerificationEntry([
    		'user_id' => $user->user_id,
			'phone_number' => $user->phone_number
    	]);

    	if(isset($verificationObject))
    	{
    		Log::notice("Verification pin created. Sending to user for phone number {$user->phone_number} verification...{$verificationObject->pin}");
			$message = "Your verification pin has been sent to {$data['phone_number']} via sms.";

			if(config("app.env") === Constants::ENV_PRODUCTION)
			{
				try {
					AuthEvents::userPhoneNumberChanged($verificationObject->to_phone_number, $verificationObject->verification_pin);
				} catch (\Exception $e){
					Log::info('Error occured');
				}
				
			}
    		return $this->apiResponse->success($message, ["data" => null] );
    	}

    	$errMsg = "Could not send verification pin to user for verification";
    	ErrorEvents::apiErrorOccurred($errMsg);
		return $this->apiResponse->generalError($errMsg);
    }

	//Logout Customer
    public function logoutUser($accessToken, $sessionId)
    {
    	$user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);

    	if($this->userRepo->logout($user->user_id, 'user_id'))
    	{
    		$message = "User : {$user->name} - {$user->phone_number} logged out successfully";
			Log::notice($message);
    		return $this->apiResponse->success($message, ["data" => null ] );
    	}

    	$message = "Unable to logout the user : {$user->name}";
		ErrorEvents::apiErrorOccurred($message);
		return $this->apiResponse->generalError($message);
    }



public function attemptSystemUserLogin(Array $data)
    {
    	// Validate request parameters
		$missingParams = Validator::validateRequiredParams($this->apiSystemUserLoginParams, $data);
		if(!empty($missingParams))
		{
			$errors = Validator::convertToRequiredValidationErrors($missingParams);
			ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

			return $this->apiResponse->validationError(
				["errors" => $errors]
			);
		}


		// Attempt to login user and get appropriate response:
		// Response -> null if unauthorized, User object if authorized
		$systemUserLogin = $this->systemUserRepo->login($data);
		if($systemUserLogin)
		{
			unset($systemUserLogin['password']);
			$message = "User : {$data['email']} successfully logged in";
			Log::notice($message);
			return $this->apiResponse->success($message, ["data" => $systemUserLogin->toArray()] );
		}
		else
		{
			ErrorEvents::apiErrorOccurred("Unauthorized login by User {$data['email']}", "warning");
			return $this->apiResponse->unauthorized();
		}
    }

    //System User Signout 

    public function logoutSystemUser($accessToken, $sessionId)
    {
    	$systemuser = $this->systemUserRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);

    	if($this->systemUserRepo->logout($systemuser->id, 'id'))
    	{
    		$message = "SystemUser : {$systemuser->name} - {$systemuser->email} logged out successfully";
			Log::notice($message);
    		return $this->apiResponse->success($message, ["data" => null ] );
    	}

    	$message = "Unable to logout the SystemUser : {$systemuser->name}";
		ErrorEvents::apiErrorOccurred($message);
		return $this->apiResponse->generalError($message);
    }


    }