<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\YearRepo;
use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Traits\YearTrait;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;




class YearActivity extends BaseActivity
{
    use YearTrait;

    protected $yearRepo;
	protected $apiResponse;

	public function __construct(YearRepo $yearRepo,ApiResponse $apiResponse)
    {
        $this->yearRepo = $yearRepo;
		$this->apiResponse = $apiResponse;
    }

    public function getYears(){
        $year_list = $this->yearRepo->yearlist();
        
        $message = "Years retrieved successfully";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $year_list] );
    }

    public function addCarYear($data)
    {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddCarYearParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

      // Check if carMake title exists if title is specified
      if(isset($data['name']))
      {
          if($this->yearRepo->yearExists($data['name']))
          {
              $message = "The specified Year {$data['name']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }
      }


      $addYear= $this->yearRepo->createYear($data);
      if($addYear)
      {
          unset($addYear['UID']);
          $message = "Year : {$data['name']} added successfully";
          Log::notice($message);
          //sAuthEvents::HasAdded($addYear);
          return $this->apiResponse->success($message, ["data" => $addYear->toArray()] );
      }
      else
      {
          $message = "Unable to add year{$data['name']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

    public function deleteCarYear($data)
    {
              // Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiDeleteCarYearParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }
      log::notice($data);
      $year = $this->yearRepo->getCarYearById($data['year_id']);
      //Check if year exist
      if(!$year){
          $message = "year not found";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->notFoundError($message);

      }

        $removeYear= $this->yearRepo->deleteCarYear($data['year_id']);
        if($removeYear)
        {
            $message = "Year : {$data['year_id']} deleted successfully";
            Log::notice($message);
            return $this->apiResponse->success($message);
        }
        else
        {
            $message = "Unable to delete for {$data['year_id']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
  
    }

    public function listCarYears($filters)
{
    // fetching Customers
    $orders = $this->yearRepo->listCarYears($filters);
    if($orders)
    {
        $message = "Year List";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $orders]);
    }

    $errMsg = "Could not get Year listings";
    ErrorEvents::apiErrorOccurred($errMsg);
    return $this->apiResponse->generalError($errMsg);
}
}