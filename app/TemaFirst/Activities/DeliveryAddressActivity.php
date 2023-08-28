<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\DeliveryAddressRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\ClientTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Repos\UserRepo;



class DeliveryAddressActivity extends BaseActivity
{
	use ClientTrait;

    protected $deliveryAddressRepo;
	protected $apiResponse;
    protected $userRepo;

	public function __construct(
        DeliveryAddressRepo $deliveryAddressRepo,
		ApiResponse $apiResponse,
        UserRepo $userRepo
	)
    {
        $this->deliveryAddressRepo = $deliveryAddressRepo;
		$this->apiResponse = $apiResponse;
        $this->userRepo = $userRepo;
    }

    public function addNewDeliveryAddress($data, $accessToken, $sessionId)
    {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddDeliveryAddressParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

      $user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);

      $addAddress= $this->deliveryAddressRepo->addAddress($data,$user->user_id);
      if($addAddress)
      {
          $message = "Delivery Address : {$data['address']} registered successfully";
          Log::notice($message);
          return $this->apiResponse->success($message, ["data" => $addAddress->toArray()] );
      }
      else
      {
          $message = "Unable to add Delivery Address{$data['address']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }


      //editing and updating categories
      public function updateDeliveryAddress($data,$id)
      {

        $addedAddress= $this->deliveryAddressRepo->updateDeliveryAddressInfo($data, $id);
        if($addedAddress)
        {
            $message = "Delivery Address : updated successfully";
            Log::notice($message);;
            return $this->apiResponse->success($message, ["data" => $addedAddress->toArray()] );
        }
        else
        {
            $message = "Unable to complete update for Delivery Address {$data['name']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
    }

            public function getDeliveryAddress($address)
            {
                // fetching Delivery Addresses
                $entity = $this->deliveryAddressRepo->getAllDeliveryAddresses($address);
                if($entity)
                {
                    $message = "Address results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $entity]);
                }
        
                $errMsg = "Could not get any Address";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }

            public function getDeliveryAddressList($accessToken,$sessionId)
            {
                
                $user = $this->userRepo->getWhereAccessTokenAndSessionId($accessToken, $sessionId);

                // fetching Delivery Addresses
                $addedAddress = $this->deliveryAddressRepo->getAllDeliveryAddresses($user->user_id);
               
                $message = "Address results";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $addedAddress]);
            }

            public function getProfile($profile)
            {
                    // Validate request parameters
                $missingParams = Validator::validateRequiredParams($this->apiGetUserProfileParams, $profile);
                if(!empty($missingParams))
                {
                    $errors = Validator::convertToRequiredValidationErrors($missingParams);
                    ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                    return $this->apiResponse->validationError(
                        ["errors" => $errors]
                    );
                }
  
                // fetching user profile
                $userProfile = $this->deliveryAddressRepo->getUserProfileById($profile['user_id']);
                if($userProfile)
                {
                    // foreach($userProfile as $userprofile){
                        unset($userProfile['access_token']);
                        unset($userProfile['session_id']);
                        unset($userProfile['session_id_time']);
                        unset($userProfile['last_logged_in']);
                    // }
                    $message = "Profile Details";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $userProfile]);
                }
        
                $errMsg = "Could not get any Profile";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function deleteDeliveryAddress($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDeleteAddressParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $address = $this->deliveryAddressRepo->getAddressById($data['address_id']);
                //Check if address exist
                if(!$address){
                    $message = "address not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }

                $removeAddress= $this->deliveryAddressRepo->deleteAddress($data['address_id']);
                if($removeAddress)
                {
                    $message = "address : {$address['address']} deleted successfully";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => null] );
                }
                else
                {
                    $message = "Unable to delete for {$address['address']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }

            public function setDefaultDeliveryAddress($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDefaultAddressParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $address = $this->deliveryAddressRepo->getAddressById($data['address_id']);

                if(!$address){
                    $message = "Address not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }
                $customer = $this->deliveryAddressRepo->getClientById($data['user_id']);
                if(!$customer){
                    $message = "Address not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }
                
                
                $deliveryAddress= $this->deliveryAddressRepo->changeAddressStatus($data['address_id'],$data['user_id']);
                Log::info($deliveryAddress);
                if($address->active == Constants::ADDRESS_STATUS_DEFAULT){
                    $message = " Address Enabled successfully";
                    return $this->apiResponse->success($message, ["data" => $deliveryAddress]);
                }else{
                    $message = " Address Disabled successfully";
                    return $this->apiResponse->success($message, ["data" => $deliveryAddress]);

                }
                Log::notice($message);
                return $this->apiResponse->success($message);
            }


            public function viewDeliveryAddress($data, $id)
            {
                // fetching Customer order items
                $address = $this->deliveryAddressRepo->viewDeliveryAddresses($data, $id);
                if($address)
                {
                    $message = "Address List";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $address]);
                }
        
                $errMsg = "Could not get Address listings";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }

}