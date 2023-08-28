<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\CarMakeRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\AuthTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Events\AuditEvent;



class carMakeActivity extends BaseActivity
{
	use AuthTrait;

    protected $carMakeRepo;
	protected $apiResponse;

	public function __construct(
        CarMakeRepo $carMakeRepo,
		ApiResponse $apiResponse
	)
    {
        $this->carMakeRepo = $carMakeRepo;
		$this->apiResponse = $apiResponse;
    }

    public function addNewCarMake($request)
    {
        $data = $request->post();
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddCarMakeParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

      // Check if carMake title exists if title is specified
      if(isset($data['title']))
      {
          if($this->carMakeRepo->carMakeExists($data['title']))
          {
              $message = "The specified  CarMake title {$data['title']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }
      }


      $addCarMake= $this->carMakeRepo->createCarMake($data);
      if($addCarMake)
      {
          unset($addCarMake['UID']);
          $message = "CarMake : {$data['title']} added successfully";
          Log::notice($message);
          
          AuditEvent::logEvent($request,$addCarMake,$message);

          return $this->apiResponse->success($message, ["data" => $addCarMake->toArray()] );
      }
      else
      {
          $message = "Unable to add carmake{$data['title']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }


      //editing and updating carmake
      public function updateCarMake($request)
      {
          $data = $request->post();
        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiEditCarMakeParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
  
            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }

            // Check if carMake title exists if title is specified
      if(isset($data['make_id']))
      {
            //check if carmake exist and update
            if(!$this->carMakeRepo->getCarMakeById($data['make_id'])){
                $message = "The specified carMake does not exists";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->notFoundError($message);
                }

      }
  
           $carmake = $this->carMakeRepo->getCarMakeById($data['make_id']);
  
              // Check if title exists if title is specified
              if(isset($data['title']) && $data['title'] != $carmake->title) 
              {
                  if($this->carMakeRepo->carMakeExists($data['title']))
                  {
                      $message = "The specified CarMake title {$data['title']} already exists";
                      ErrorEvents::apiErrorOccurred($message, "warning");
                      return $this->apiResponse->generalError($message);
                  }
              }
              $addedCarMake= $this->carMakeRepo->updateCarMakeInfo($data, $carmake->id);
              if($addedCarMake)
              {
                  unset($addedCarMake['UID']);
                  $message = "Carmake : updated successfully to {$data['title']}";

                  AuditEvent::logEvent($request,$addedCarMake,$message);

                  Log::notice($message);
                  return $this->apiResponse->success($message, ["data" => $addedCarMake->toArray()] );
              }
              else
              {
                  $message = "Unable to complete update for carmake {$data['title']}";
                  ErrorEvents::apiErrorOccurred($message);
                  return $this->apiResponse->generalError($message);
              }
            }

            public function getCarMakes($carmakes)
            {
                // fetching carmakes
                $entity = $this->carMakeRepo->getAllCarMakes($carmakes);
                if($entity)
                {
                    $message = "CarMake results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $entity]);
                }
        
                $errMsg = "Could not get Carmakes";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function removeCarMake($request)
            {
                $data = $request->post();
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDeleteCarMakeParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
              log::notice($data);
              $carmake = $this->carMakeRepo->getCarMakeById($data['make_id']);
              //Check if carmake exist
              if(!$carmake){
                  $message = "CarMake not found";
                  ErrorEvents::apiErrorOccurred($message);
                  return $this->apiResponse->notFoundError($message);

              }

              //Check if carmake is having children
              if($carmake->carModels != null && $carmake->carModels->count() > 0){
                  $message = "Unable to delete {$carmake['title']}, car make has car model";
                  ErrorEvents::apiErrorOccurred($message);
                  return $this->apiResponse->generalError($message);
              }
                $removeCarMake= $this->carMakeRepo->deleteCarMake($data['make_id']);
                if($removeCarMake)
                {
                    $message = "CarMake : {$carmake['title']} deleted successfully";
                    
                    AuditEvent::logEvent($request,$carmake,$message);

                    Log::notice($message);
                    return $this->apiResponse->success($message);
                }
                else
                {
                    $message = "Unable to delete for {$data['make_id']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }

    public  function listMakeWithModels($filters)
    {
        $carMakeModels = $this->carMakeRepo->listMakeWithModels($filters);

        $message = "Showing car makes";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $carMakeModels]);
    }

}