<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\SystemUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Activities\RoleActivity;

use Redirect;
use Session;
use Excel;

class RoleController extends Controller {

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $roleActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        RoleActivity $roleActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->roleActivity = $roleActivity;
    }


    public function getAllPermissionsGroup(){
        Log::notice("logging permissions");
        try
        {
            return  $this->roleActivity->getAllPermissionsGroup();
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function editSystemUserRole(Request $request)
    {
        try
        {
            return  $this->roleActivity->updateSystemUserRole($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function viewRoleDetails(Request $request,$id)
    {
        try
        {
            return  $this->roleActivity->viewRoleDetails($request->post(),$id);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function searchSystemUserRole(Request $request)
    {
        try
        {
            return  $this->roleActivity->systemUserSearchRole($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteSystemUserRole(Request $request, $id)
    {
        try
        {
            return  $this->roleActivity->deleteSystemUserRole($id);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function addSystemUserRole(Request $request)
    {
        try
        {
            return  $this->roleActivity->addSystemUserRole($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function SystemUserExportRoles(Request $request)
    {
        try
        {
            return  $this->roleActivity->exportSystemUsersRoles($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }



    public function getAllRoles(Request $request){

        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            return  $this->roleActivity->getRoles($request->post(),$accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }
}
