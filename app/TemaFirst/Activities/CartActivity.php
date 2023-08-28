<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\CartRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\CartTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Repos\AutoPartsRepo;



class CartActivity extends BaseActivity
{
	use CartTrait;

    protected $cartRepo;
	protected $apiResponse;
    protected $autoPartRepo;

	public function __construct(
        CartRepo $cartRepo,
		ApiResponse $apiResponse,
        AutoPartsRepo $autoPartRepo
	)
    {
        $this->cartRepo = $cartRepo;
		$this->apiResponse = $apiResponse;
        $this->autoPartRepo = $autoPartRepo;
    }

    public function addNewCartItem(Request $request, $data)
    {
        $deviceID = $request->headers->get('device-id');
        $customerID = $request->headers->get('customer-id');

        if($deviceID === null) {
            Log::warning("No device ID header values present, throwing forbidden device id not found...");
            return $this->apiResponse->notFoundError("Device ID is not found");
        }

        Log::notice($deviceID);    
        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiAddCartItemParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }

        $autoPart = $this->autoPartRepo->getAutoPartByid($data['part_id']);
        //Check if autopart exist
        if(!$autoPart){
            $message = "Auto  Part not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);

        }

        $addItem= $this->cartRepo->addCartItem($data,$deviceID,$customerID,$autoPart);
        if($addItem)
        {
            $message = "Delivery Item added to cart";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $addItem->toArray()] );
        }
        else
        {
            $message = "Unable to add item to cart";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
        }


        public function mGetCart($request)
        {
            $deviceID = $request->headers->get('device-id');
            $customerID = $request->headers->get('customer-id');

            // fetching Customers
            $cart = $this->cartRepo->mGetCart($customerID,$deviceID);
            if($cart)
            {
                $message = "Cart List";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $cart]);
            }
    
            $errMsg = "Could not get cart listings";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }

        public function viewUserCartItems($data)
        {
                 // Validate request parameters
            $missingParams = Validator::validateRequiredParams($this->apiGetCustomerParams, $data);
            if(!empty($missingParams))
            {
                $errors = Validator::convertToRequiredValidationErrors($missingParams);
                ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

                return $this->apiResponse->validationError(
                    ["errors" => $errors]
                );
            }
            log::notice($data);
            $customer = $this->cartRepo->getCustomerById($data['user_id']);

            if(!$customer){
                $message = "The specified customer does not exist";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
                }
            // fetching Customer cart items
            $carts = $this->cartRepo->viewUserCartItems($data['user_id']);
            if($carts)
            {
                $message = "Cart List";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $carts]);
            }
    
            $errMsg = "Could not get cart listings";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }


        public function getCartItemsList($filters)
        {
  
             //fetching Customers
            $carts = $this->cartRepo->getAllCartItemsList($filters);
            if($carts)
            {
                $message = "Cart Listings";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $carts]);
            }
    
            $errMsg = "Could not get cart listings";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }


    

            public function deleteCartItem($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiRemoveCartItemParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $cartItem = $this->cartRepo->getCartItemById($data['cart_item_id']);
                //Check if cartItem exist
                if(!$cartItem){
                    $message = "cartItem not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }

                $removeItem= $this->cartRepo->deleteCartItem($data['cart_item_id']);
                if($removeItem)
                {
                    $message = "Item removed successfully";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => null] );
                }
                else
                {
                    $message = "Unable to remove Item";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }


            public function removeCart($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiRemoveCartParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $cart = $this->cartRepo->getCartById($data['cart_id']);
                //Check if cartItem exist
                if(!$cart){
                    $message = "cart not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }

                $removeCart= $this->cartRepo->removeCart($data['cart_id']);
                if($removeCart)
                {
                    $message = "cart removed successfully";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => null] );
                }
                else
                {
                    $message = "Unable to remove cart";
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
                $address = $this->cartRepo->getAddressById($data['address_id']);
                
                $publishPart= $this->cartRepo->changeAddressStatus($data['address_id']);
                if($address->status == Constants::STATUS_DISABLED){
                    $message = " Address Enabled successful";
                }else{
                    $message = " Address Disabled successful";
                }
                Log::notice($message);
                return $this->apiResponse->success($message);
            }


            public function searchCarts($data)
            {
              // Attempt to search Carts
              $searchCart= $this->cartRepo->searchCarts($data);
              if($searchCart)
              {
                  $message = "Cart : search results";
                  Log::notice($message);
                  return $this->apiResponse->success($message, ["data" => $searchCart] );
              }
              else
              {
                  $message = "Unable to fetch data";
                  ErrorEvents::apiErrorOccurred($message);
                  return $this->apiResponse->generalError($message);
              }
            }


}