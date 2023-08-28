<?php

namespace App\Http\Controllers;
use App\TemaFirst\Activities\AuditTrailActivity;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\SystemUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Activities\RoleActivity;

use Redirect;
use Session;
use Excel;

class AuditTrailController extends Controller
{
    protected $auditTrailActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        AuditTrailActivity $auditTrailActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->auditTrailActivity = $auditTrailActivity;
    }

    public function searchSystemUserAudit(Request $request)
    {
        try
        {
            return  $this->auditTrailActivity->systemUserSearchAudit($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }
  public function SystemUserExportAudit(Request $request)
    {
        try
        {
            return  $this->auditTrailActivity->exportSystemUsersAudit($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }
 public function getAllAudit(Request $request){

        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            return  $this->auditTrailActivity->getAuditTrails($request->post(),$accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

        // $activities=Activity::All();
   
        // return json_encode(["result" => $activities],200);
        
    }
}
