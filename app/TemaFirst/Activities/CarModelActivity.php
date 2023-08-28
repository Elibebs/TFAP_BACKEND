<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\CarModelRepo;
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



class CarModelActivity extends BaseActivity
{
	use AuthTrait;

    protected $carModelRepo;
    protected $carMakeRepo;
	protected $apiResponse;

	public function __construct(
        CarModelRepo $carModelRepo,
        CarMakeRepo $carMakeRepo,
		ApiResponse $apiResponse
	)
    {
        $this->carModelRepo = $carModelRepo;
        $this->carMakeRepo = $carMakeRepo;
		$this->apiResponse = $apiResponse;
    }

    public function addNewCarModel($data)
    {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddCarModelParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

    //   //check if carmake
    //   if(!$this->carModelRepo->getCarMakeById($data['make_id'])){
    //     $message = "The specified carmake does not exists";
    //     ErrorEvents::apiErrorOccurred($message, "warning");
    //     return $this->apiResponse->generalError($message);
    //   }

    //         //check if carYear
    //         if(!$this->carModelRepo->getCarYearById($data['year_id'])){
    //             $message = "The specified carYear does not exists";
    //             ErrorEvents::apiErrorOccurred($message, "warning");
    //             return $this->apiResponse->generalError($message);
    //           }

    //   // Check if carmake name exists if email is specified
  
    //       if($this->carModelRepo->carModelExists($data['name']))
    //       {
    //           $message = "The specified name {$data['name']} already exists";
    //           ErrorEvents::apiErrorOccurred($message, "warning");
    //           return $this->apiResponse->generalError($message);
    //       }
        if($this->carModelRepo->carModelExists($data['name'] , $data['year_id']))
        {
            $message = "The specified name {$data['name']} and year {$data['year_id']} already exists";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->generalError($message);
        }


        $addCarModel= $this->carModelRepo->createCarModel($data);
        if($addCarModel)
        {
            unset($addCarModel['UID']);
            $message = "CarModel : {$data['name']} added successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $addCarModel->toArray()] );
        }
        else
        {
            $message = "Unable to add carModel{$data['name']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
        }


      //editing and updating categories
   public function updateCarModel($data)
      {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiEditCarModelParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

    //   //check if carmake
    //   if(!$this->carModelRepo->getCarMakeById($data['make_id'])){
    //     $message = "The specified carmake does not exists";
    //     ErrorEvents::apiErrorOccurred($message, "warning");
    //     return $this->apiResponse->generalError($message);
    //   }

      // Check if carmodel name exists if name is specified
  
          if($this->carModelRepo->carModelExists($data['name'] , $data['year_id']))
          {
              $message = "The specified name {$data['name']} and year {$data['year_id']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }

    //     //check if carYear
    //     if(!$this->carModelRepo->getCarYearById($data['year_id'])){
    //     $message = "The specified carYear does not exists";
    //     ErrorEvents::apiErrorOccurred($message, "warning");
    //     return $this->apiResponse->generalError($message);
    //     }

        // if(isset($data['name']) && $data['year_id'] == $carmake->name && $carmake->year_id) 
        // {
        //     if($this->carMakeRepo->carMakeExists($data['name']))
        //     {
        //         $message = "The specified CarModel name {$data['name']} already exists";
        //         ErrorEvents::apiErrorOccurred($message, "warning");
        //         return $this->apiResponse->generalError($message);
        //     }
        // }
    
        $entity = $this->carModelRepo->getCarModelById($data['car_model_id']);

        $addedCarModel= $this->carModelRepo->updateCarModel($data,$entity->id);
        if($addedCarModel)
        {
            unset($addedCarModel['UID']);
            $message = "CarModel : {$data['name']} updated successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $addedCarModel->toArray()] );
        }
        else
        {
            $message = "Unable to add carModel{$data['name']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
 }


            public function getCarModel($car_models)
            {
                // fetching subcategories
                $entity = $this->carModelRepo->getAllCarModels($car_models);
                if($entity)
                {
                    $message = "CarModels results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $entity]);
                }
        
                $errMsg = "Could not get CarModel";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function removeCarModel($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDeleteCarModelParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $car_models = $this->carModelRepo->getCarModelById($data['model_id']);
                
                $RemoveCarModel= $this->carModelRepo->deleteCarModel($data);
                if($RemoveCarModel)
                {
                    $message = "CarModel : {$data['model_id']} deleted successfully";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $RemoveCarModel->toArray()] );
                }
                else
                {
                    $message = "Unable to delete for {$data['model_id']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }

            public function searchMakeModelYear($data)
            {
                // Attempt to search Parts
                $searchMakeModelYear= $this->carModelRepo->searchMakeModelYear($data);
                if($searchMakeModelYear)
                {
                    $message = "search results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $searchMakeModelYear]);
                }
                else
                {
                    $message = "Unable to fetch data";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
            }

}