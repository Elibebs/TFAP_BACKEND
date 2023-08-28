<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\SystemUser;
use App\Models\Role;
use App\Activity;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\SystemUserActivity;
use App\TemaFirst\Activities\AuthActivity;

use Redirect;
use Session;
use Excel;

class SystemUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $systemUserActivity;
    protected $apiResponse;
    protected $authActivity;

	public function __construct(
        ApiResponse $apiResponse,
        SystemUserActivity $systemUserActivity,
        AuthActivity $authActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->systemUserActivity = $systemUserActivity;
        $this->authActivity = $authActivity;
    }

    
    public function index()
    {
        //
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request)
	{
		try
		{
            return  $this->authActivity->attemptSystemUserLogin($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function logout(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            return  $this->authActivity->logoutSystemUser($accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }


    public function viewUserDetails(Request $request){

        try
        {
            return  $this->systemUserActivity->getUserDetails($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function searchCustomers(Request $request)
    {
        try
        {
            return  $this->systemUserActivity->customerSearch($request-post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }
    
    public function addSystemUser(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');

            return  $this->systemUserActivity->addSystemUser($request,$accessToken,$sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listSystemUsers(Request $request){

        try
        {
            return  $this->systemUserActivity->getSystemUsers($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function listCustomers(Request $request){

        try
        {
            return  $this->systemUserActivity->getCustomers($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function disableCustomer(Request $request)
    {
        try
        {
            return  $this->systemUserActivity->changeCustomerStatus($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function editSystemUser(Request $request,$id)
    {
        try
        {
            return  $this->systemUserActivity->updateSystemUser($request,$id);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function searchSystemUser(Request $request)
    {
        try
        {
            return  $this->systemUserActivity->systemUserSearch($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function SystemUserExport(Request $request)
    {
        try
        {
            return  $this->systemUserActivity->exportSystemUsers($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function disableSystemUser(Request $request)
    {
        try
        {
            return  $this->systemUserActivity->disableSystemUser($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function changeSystemUserPassword(Request $request)
    {
        try
        {
            // $accessToken = $request->header('access-token');
            // $sessionId = $request->header('session-id');
            return  $this->systemUserActivity->changeSystemUserPassword($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function systemUserPasswordReset(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            return  $this->systemUserActivity->systemUserPasswordReset($request,$accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function delete(Request $request, $id)
    {
        try{
            return $this->systemUserActivity->deleteUser($request, $id);
        }catch(\Exception $e){
            return $this->apiResponse->serverError();
        }

    }
    public function customerType(Request $request){

        try
        {
            return  $this->systemUserActivity->customerType($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }
}
