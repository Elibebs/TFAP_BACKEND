<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\OrderRepo;
use App\TemaFirst\Repos\CartRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\OrderTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;



class OrderActivity extends BaseActivity
{
	use OrderTrait;

    protected $orderRepo;
    protected $cartRepo;
	protected $apiResponse;

	public function __construct(
        OrderRepo $orderRepo,
        CartRepo $cartRepo,
		ApiResponse $apiResponse
	)
    {
        $this->orderRepo = $orderRepo;
        $this->cartRepo = $cartRepo;
		$this->apiResponse = $apiResponse;
    }

    public function addNewOrder(Request $request, $data)
    {
    
        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiAddOrderItemParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
        $shippingId = $this->orderRepo->getShippingInfoById($data['shipping_id']);
        //Check if shipping info exist
        if(!$shippingId){
            $message = "Shipping Info not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);

        }

        $cart = $this->cartRepo->getCartByDeviceId($data['device_id']);
        //Check if device ID exist
        if(!$cart){
            $message = "Cart with this device Id not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);

        }
        //Check if device ID exist
        if(count($cart->cartItems)<=0){
            $message = "cartItems not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);

        }



        $customerId = $this->orderRepo->getCustomerById($data['customer_id']);
        //Check if customerId exist
        if(!$customerId){
            $message = "Customer Info not found";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->notFoundError($message);

        }

        $addOrder= $this->orderRepo->addOrder($data, $cart);
        if($addOrder)
        {
            $message = "Order Submitted Successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $addOrder->toArray()] );
        }
        else
        {
            $message = "Unable to add item for oder";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
        }


        public function listOrders($filters)
        {
            // fetching Customers
            $orders = $this->orderRepo->listOrders($filters);
            if($orders)
            {
                $message = "Order List";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $orders]);
            }
    
            $errMsg = "Could not order listings";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }

        public function viewOrders($data)
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
            $customer = $this->orderRepo->getCustomerById($data['customer_id']);

            if(!$customer){
                $message = "The specified customer does not exist";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
                }
            // fetching Customer order items
            $orders = $this->orderRepo->viewOrders($data['customer_id']);
            if($orders)
            {
                $message = "Order List";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $orders]);
            }
    
            $errMsg = "Could not get Order listings";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }

        public function searchOrders($data)
        {
          // Attempt to search Carts
          $searchOrder= $this->orderRepo->searchOrders($data);
          if($searchOrder)
          {
              $message = "Order : search results";
              Log::notice($message);
              return $this->apiResponse->success($message, ["data" => $searchOrder] );
          }
          else
          {
              $message = "Unable to fetch data";
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
                $address = $this->orderRepo->getAddressById($data['address_id']);
                
                $publishPart= $this->orderRepo->changeAddressStatus($data['address_id']);
                if($address->status == Constants::STATUS_DISABLED){
                    $message = " Address Enabled successful";
                }else{
                    $message = " Address Disabled successful";
                }
                Log::notice($message);
                return $this->apiResponse->success($message);
            }
}