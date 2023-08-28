<?php

namespace App\TemaFirst\Activities;

use Carbon\Carbon;
use App\TemaFirst\Repos\SystemUserRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\AuthTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\AuditEvent;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Mail\WelcomeMail;
use App\Notifications\WelcomeUser;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\Models\SytstemUser;
use Validator as CValidator;
use App\TemaFirst\Repos\RoleRepo;


class SystemUserActivity extends BaseActivity
{
	use AuthTrait;

    protected $systemUserRepo;
    protected $verificationRepo;
    protected $apiResponse;
    protected $roleRepo;

	public function __construct(
        SystemUserRepo $systemUserRepo,
        RoleRepo $roleRepo,
        VerificationRepo $verificationRepo,
		ApiResponse $apiResponse
	)
    {
        $this->systemUserRepo = $systemUserRepo;
        $this->verificationRepo = $verificationRepo;
        $this->apiResponse = $apiResponse;
        $this->roleRepo  = $roleRepo;
    }


        public function addSystemUser($request,$accessToken,$sessionId)
    {
        $data = $request->post();
        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiAddSystemUserParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->generalError($message);
        }


        // Check if email exists if email is specified
        if(isset($data['email']))
        {
            if($this->systemUserRepo->emailExists($data['email']))
            {
                $message = "The specified email {$data['email']} already exists";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
            }
        }



        $systemUser = $this->systemUserRepo->getWhereAccessTokenAndSessionId($accessToken,$sessionId);
        if(!isset($systemUser))
        {
            $message = "System user not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

    
        if(!$systemUser->hasRole('SuperAdmin'))
        {
            $roleIds = explode(",", $data['role_id']);
            $superAdminRole = $this->roleRepo->getSuperAdminRole();
            foreach ($roleIds as $roleid) 
            {
                if($superAdminRole->id == $roleid){
                    $message = "Unauthorize, you cannot create super admin account";
                    ErrorEvents::apiErrorOccurred($message, "warning");
                    return $this->apiResponse->generalError($message);
                }
            }
        }


        $addedSystemUser= $this->systemUserRepo->createSystemUser($data);
        if($addedSystemUser)
        {
            unset($addedSystemUser['password']);
            $message = "SystemUser : {$data['email']} registered successfully";
            AuditEvent::logEvent($request,$addedSystemUser,$message);
            //Log::notice($message);
            AuthEvents::systemUserHasAdded($addedSystemUser);
            return $this->apiResponse->success($message, ["data" => $addedSystemUser->toArray()] );
        }
        else
        {
            $message = "Unable to complete registration for {$data['email']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
    }


    public function disableSystemUser($request)
    {
        $data = $request->post();
          	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddSystemUserDisableParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }
    	//log::notice($data);
        $systemuser = $this->systemUserRepo->getSystemUserById($data['system_user_id']);
        
        $changedSystemUserStatus= $this->systemUserRepo->changeSystemUserStatus($data['system_user_id']);
        if($systemuser->status == Constants::STATUS_DISABLED){
            $message = " System User: {$systemuser->name} Enabled successful";
            AuditEvent::logEvent($request,$systemuser,$message);
        }else{
            $message = " System User: {$systemuser->name} Disabled successful";
            AuditEvent::logEvent($request,$systemuser,$message);
        }
       // Log::notice($message);
        return $this->apiResponse->success($message);
    }

    public function changeCustomerStatus($data)
    {
          	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiChangeCustomerStatusParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }
    	//log::notice($data);
        $customer = $this->systemUserRepo->getCustomerById($data['user_id']);
        
        $changedSystemUserStatus= $this->systemUserRepo->changeUserStatus($data['user_id']);
        if($customer->status == Constants::STATUS_DISABLED){
            $message = "User Enabled successful";
        }else{
            $message = "User Disabled successful";
        }
        //Log::notice($message);
        return $this->apiResponse->success($message);
    }

    public function getSystemUsers($filters)
    {
        // fetching Systemusers
        $systemUsers = $this->systemUserRepo->getAllSystemUsers($filters);
        if($systemUsers)
        {
            foreach($systemUsers as $systemuser){
                unset($systemuser['access_token']);
                unset($systemuser['session_id']);
                unset($systemuser['session_id_time']);
                unset($systemuser['last_logged_in']);
            }

            $message = "systemusers results";
            //Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $systemUsers]);
        }

        $errMsg = "Could not get SystemUsers";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }

    public function getCustomers($systemusers)
    {
        // fetching Customers
        $systemUsers = $this->systemUserRepo->getAllCustomers();
        if($systemUsers)
        {
            foreach($systemUsers as $systemuser){
                unset($systemuser['access_token']);
                unset($systemuser['session_id']);
                unset($systemuser['session_id_time']);
                unset($systemuser['last_logged_in']);
            }

            $message = "Customers List";
            //Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $systemUsers]);
        }

        $errMsg = "Could not get Customers";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }

    public function getUserDetails($request)
    {
        $data = $request->post();
            // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiGetUserProfileParams, $details);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }

        // fetching user profile
        $userProfile = $this->systemUserRepo->getUserProfileById($details['user_id']);
        if($userProfile)
        {
            // foreach($userProfile as $userprofile){
                unset($userProfile['access_token']);
                unset($userProfile['session_id']);
                unset($userProfile['session_id_time']);
                unset($userProfile['last_logged_in']);
            // }
            $message = "User Details";
            AuditEvent::logEvent($request,$userProfile,$message);

           // Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $userProfile]);
        }

        $errMsg = "Could not get any User Detail";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }

    public function customerSearch($data)
    {
      // Attempt to search Customer
      $searchUser= $this->systemUserRepo->searchCustomer($data);
      if($searchUser)
      {
          $message = "Customer : search results";
         // Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $searchUser] );
      }
      else
      {
          $message = "Unable to fetch data";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }



   
      //editing and updating systemuser
    public function updateSystemUser($request,$id)
    {
        $data = $request->post();

         $systemUser = $this->systemUserRepo->getSystemUserById($id);

         if(!$systemUser)
         {
            $message = "User with id {$id} does not exist";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->notFoundError($message);
         }

        // Check if email exists if email is specified
        if(isset($data['email']) && $data['email'] != $systemUser->email) 
        {
            if($this->systemUserRepo->systemUserEmailExists($data['email']))
            {
                $message = "The specified Email {$data['email']} already exists";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
            }
        }
            
        if(!$systemUser->hasRole('SuperAdmin') && isset($data['role_id']))
        {
            $roleIds = explode(",", $data['role_id']);
            $superAdminRole = $this->roleRepo->getSuperAdminRole();
            foreach ($roleIds as $roleid) 
            {
                if($superAdminRole->id == $roleid){
                    $message = "Unauthorize, you cannot create super admin account";
                    ErrorEvents::apiErrorOccurred($message, "warning");
                    return $this->apiResponse->generalError($message);
                }
            }
        }
   
      $addedSystemUser= $this->systemUserRepo->updateSystemUser($data, $systemUser->id);
      if($addedSystemUser)
      {
          unset($addedSystemUser['password']);
          $message = "SystemUser : {$addedSystemUser['name']} updated successfully";
          AuditEvent::logEvent($request,$addedSystemUser,$message);
         // Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $addedSystemUser->toArray()] );
      }else{
          $message = "Unable to complete update system user";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

  
          //reset 

    public function systemUserPasswordReset($request)
    {
        $data = $request->post();
          	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiResetPasswordParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

         $systemuser = $this->systemUserRepo->getSystemUserById($data['system_user_id']);

         if(!$systemuser)
         {
            $message = "SystemUser: with id {$data['system_user_id']} does not exist";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->notFoundError($message);
         }

		// reset systemuser password
        $resetPassword= $this->systemUserRepo->systemUserPasswordReset($data, $systemuser->id);
        if($resetPassword)
        {
                // $token = app('auth.password.broker')->createToken($systemuser);
            $systemuser->notify(new ResetPassword($resetPassword->token));

            $message = "SystemUser : password reset link sent to {$systemuser->email}";
            AuditEvent::logEvent($request,$resetPassword,$message);

            //Log::notice($message);
            return $this->apiResponse->success($message);
        }
        else
        {
            $message = "Unable to reset password";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
    }
  

    public function systemUserSearch($request)
    {
        $data = $request->post();
      // Attempt to search role
      $searchedSystemUser= $this->systemUserRepo->searchSystemUser($data);
      if($searchedSystemUser)
      {
          $message = "SystemUser : search results";
          //Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $searchedSystemUser] );
      }
      else
      {
          $message = "Unable to fetch data";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

    public function exportSystemUsers($systemuser)
    {
      // Attempt to search role
      $exportedSystemUser= $this->systemUserRepo->systemUsersExporter($systemuser);
      if($exportedSystemUser)
      {
          $message = "SystemUser : exported results";
          //Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $exportedSystemUser] );
      }
      else
      {
          $message = "Unable to export data";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }


    public function changeSystemUserPassword($request){
        
         $data = $request->post();
       // Log::error($data);
        $missingParams = Validator::validateRequiredParams($this->apiSystemUserChangePasswordParams, $data);
        if(!empty($missingParams)){

            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(["errors" => $errors]);
        }

        //Check for system user using email provided
        $member = $this->systemUserRepo->getMemberByEmail($data['email']);
        if(!isset($member)) {
            $message = "Member with Email '{$data["email"]}' does not exist";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);
        }


        //Get password reset for token and email
        $password_reset = $this->systemUserRepo->getMemberPasswordReset($data['token'],$data['email']);
        if(!isset($password_reset)) {
            $message = "Invalid token or email provided. Please try again";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

        //Validate Token
        if(Carbon::parse($password_reset->created_at) < Carbon::now()){
            $message = "Token '{$password_reset->token}' has expired";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

        //Check if password and confirm_password are the same
        if($data['new_password'] != $data['confirm_password']){
            $message = "Passwords do not much";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

        //Change Password
        if($this->systemUserRepo->changeMemberPassword($data,$member)){
            $message = "Member : {$member->name} - {$member->phone_number} password successfuly changed.";
            AuditEvent::logEvent($request,$password_reset,$message);

            //Log::notice($message);
            return $this->apiResponse->success($message, ["data" => null ] );
        }

        $message = "Something went wrong while changing your password";
        ErrorEvents::apiErrorOccurred($message);
        return $this->apiResponse->generalError($message);

    }


    public function deleteUser($request,$id)
    {
        $data = $request->post();
        $systemUser = $this->systemUserRepo->getSystemUserById($id);
        if(!$systemUser){
            $message = "The specified user does not exists";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->notFoundError($message);  
        } 

        if($this->systemUserRepo->deleteUser($id)){
            $message = "SystemUser: {$systemUser->name} deleted successfully";
            AuditEvent::logEvent($request,$systemUser,$message);
			//Log::notice($message);
			return $this->apiResponse->success($message, ["data" => null] );
        }

        $errMsg = "Could not delete system user";
		ErrorEvents::apiErrorOccurred($errMsg);
		return $this->apiResponse->generalError($errMsg);
    }

    public function customerType($filters)
	{

		$userSegregation = $this->systemUserRepo->customerType($filters);
	   
		$message = "showing customer segregations";
		//Log::notice($message);
		return $this->apiResponse->success($message, ["data" => $userSegregation]);
		
	}


    }