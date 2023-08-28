<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\RoleRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Repos\SystemUserRepo;
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


class RoleActivity extends BaseActivity
{
	use AuthTrait;

    protected $roleRepo;
    protected $systemUserRepo;
    protected $verificationRepo;
	protected $apiResponse;

	public function __construct(
        RoleRepo $roleRepo,
        SystemUserRepo $systemUserRepo,
        VerificationRepo $verificationRepo,
		ApiResponse $apiResponse
	)
    {
        $this->roleRepo = $roleRepo;
        $this->verificationRepo = $verificationRepo;
        $this->systemUserRepo = $systemUserRepo;
		$this->apiResponse = $apiResponse;
    }


    public function deleteSystemUserRole($id)
    {
        $role = $this->roleRepo->getRoleById($id);

        if(!$role)
        {
            $message = "Role not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);
        }
        
        if($this->roleRepo->SystemUserRoleDelete($role->id))
        {
            $message = "Role deleted successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => null]);
        }
        else
        {
            $message = "Unable to delete for {$data['role_id']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
  
    }

  public function updateSystemUserRole($data)
    {
    	// Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiUpdateSystemUserRoleParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
  
            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
  

      // Check if role exists if rolename is specified
      $role = $this->roleRepo->getRoleById($data['role_id']);

      if(!$role){
        $message = "The specified role do not exist";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
        }

    //   $permissions = $this->roleRepo->getPermsById($data['permissions']);
    //   if(!$permissions){
    //     $message = "The specified perm or permissions do not exist";
    //     ErrorEvents::apiErrorOccurred($message, "warning");
    //     return $this->apiResponse->notFoundError($message);
    //     }

      // Check if name exists if email is specified
      if(isset($data['name']) && $data['name'] != $role->name) 
      {
          if($this->roleRepo->roleExists($data['name']))
          {
              $message = "The specified Role {$data['name']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }
      }


   

      $addedSystemUserRole= $this->roleRepo->updateSystemUserRole($data, $role->id);
      if($addedSystemUserRole)
      {
          $message = "Role : {$data['name']} updated successfully";
          Log::notice($message);
          AuthEvents::systemUserRoleHasAdded($addedSystemUserRole);
          return $this->apiResponse->success($message, ["data" => $addedSystemUserRole->toArray()] );
      }
      else
      {
          $message = "Unable to complete update for {$data['name']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

  public function addSystemUserRole($data)
    {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddSystemUserRoleParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

      // Check if role exists if rolename is specified
      if(isset($data['name']))
      {
          if($this->roleRepo->roleExists($data['name']))
          {
              $message = "The specified Role name {$data['name']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }
      }

      // Attempt to add role
      $addedSystemUserRole= $this->roleRepo->createSystemUserRole($data);
      if($addedSystemUserRole)
      {
          $message = "SystemUser : {$data['name']} added successfully";
          Log::notice($message);
          AuthEvents::systemUserRoleHasAdded($addedSystemUserRole);
          return $this->apiResponse->success($message, ["data" => $addedSystemUserRole->toArray()] );
      }
      else
      {
          $message = "Unable to  add role {$data['name']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }
public function exportSystemUsersRoles($systemuser)
    {
      // Attempt to search role
      $exportedSystemUser= $this->roleRepo->systemUsersRolesExporter($systemuser);
      if($exportedSystemUser)
      {
          $message = "SystemUser : Roles exported ";
          Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $exportedSystemUser] );
      }
      else
      {
          $message = "Unable to export data";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

    public function systemUserSearchRole($data)
    {
      // Attempt to search role
      $searchedSystemUserRole= $this->roleRepo->searchSystemUserRole($data);
      if($searchedSystemUserRole)
      {
          $message = "SystemUser : search role results";
          Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $searchedSystemUserRole] );
      }
      else
      {
          $message = "Unable to fetch data";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

    public function getRoles($filters,$accessToken,$sessionId)
    {

        //$roles = $this->roleRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);
        //First get systemm user
        $systemUser = $this->systemUserRepo->getWhereAccessTokenAndSessionId($accessToken,$sessionId);
        if(!isset($systemUser))
        {
            $message = "System user not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
        $roles = [];
        //Check if user is a SuperAdmin
        if($systemUser->hasRole('SuperAdmin'))
        {
            $roles = $this->roleRepo->getAllRoles($filters);
        }else
        {
            $roles = $this->roleRepo->getAllNonSuperAdminRoles($filters);
        }

		if($roles)
		{
			$message = "Role results";
			Log::notice($message);
			return $this->apiResponse->success($message, ["data" => $roles]);
		}

		$errMsg = "Could not get Roles";
		ErrorEvents::apiErrorOccurred($errMsg);
		return $this->apiResponse->generalError($errMsg);
    }

    public function viewRoleDetails($data, $id)
    {
        // fetching Customer order items
        $role = $this->roleRepo->viewRoleDetails($data, $id);
        if($role)
        {
            $role_permissions = $role->permissions;
            unset($role->permissions);

            $role["permissions"] = $this->groupPermissions($role_permissions);
            $message = "role details";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $role]);
        }

        $errMsg = "Could not get role details";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }

    public function getAllPermissionsGroup()
    {
        $permissions = $this->groupPermissions($this->roleRepo->getAllPermissions());

        $message = "Permission list retrieved successfully";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $permissions]);

    }

    private function groupPermissions($permissions)
    {
      //Log:info($permissions);
        $permissions_data = [];
        $permission_group = [];
        $active_perm_name = '';
        foreach($permissions as $permission)
        {
            $permission_name = explode(':',$permission->name);
            //Log::info($permission_name);
            
            if($active_perm_name !== $permission_name[1]){
                
                if(count($permission_group) > 0){
                
                    $permissions_data[$active_perm_name] = $permission_group;
                    $permission_group = [];
                }//else{
                    ///Should add first item in the list
                   // array_push($permission_group,$permission_name[1]);
                   $active_perm_name = $permission_name[1];
                    array_push($permission_group,$permission);
                //}
                //$active_perm_name = $permission_name[1];
                
            }else{
                array_push($permission_group,$permission);
            }
        }

        if(count($permission_group) > 0)
        {
            $permissions_data[$active_perm_name] = $permission_group;
        }

        return $permissions_data;
    }
}