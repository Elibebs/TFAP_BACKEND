<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\AuditTrailRepo;
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


class AuditTrailActivity extends BaseActivity
{
	use AuthTrait;

    protected $auditTrailRepo;
    protected $verificationRepo;
	protected $apiResponse;

	public function __construct(
        AuditTrailRepo $auditTrailRepo,
        VerificationRepo $verificationRepo,
		ApiResponse $apiResponse
	)
    {
        $this->auditTrailRepo = $auditTrailRepo;
        $this->verificationRepo = $verificationRepo;
		$this->apiResponse = $apiResponse;
    }

    public function getAuditTrails($filters, $accessToken, $sessionId)
    {

		$activities = $this->auditTrailRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);

		// fetching Audit Trail
		$entity = $this->auditTrailRepo->getAllAuditTrails($filters);
		if($entity)
		{
			$message = "Audit Trail results";
			Log::notice($message);
			return $this->apiResponse->success($message, ["data" => $entity]);
		}

		$errMsg = "Could not get Audit Trails";
		ErrorEvents::apiErrorOccurred($errMsg);
		return $this->apiResponse->generalError($errMsg);
    }

 public function exportSystemUsersAudit($systemuser)
    {
      // Attempt to search role
      $exportedSystemUser= $this->auditTrailRepo->systemUsersAuditTrailExporter($systemuser);
      if($exportedSystemUser)
      {
          $message = "SystemUser : Audit Trail exported ";
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
 public function systemUserSearchAudit($data)
    {
      // Attempt to search role
      $searchedSystemUserAudit= $this->auditTrailRepo->searchSystemUserAuditTrail($data);
      if($searchedSystemUserAudit)
      {
          $message = "SystemUser : Audit Trail search results";
          Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $searchedSystemUserAudit] );
      }
      else
      {
          $message = "Unable to fetch data";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }


}