<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\DeliveryAddressActivity;

use Redirect;
use Session;
use Excel;

class DeliveryAddressController extends Controller
{

    protected $deliveryAddressActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        DeliveryAddressActivity $deliveryAddressActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->deliveryAddressActivity = $deliveryAddressActivity;
    }

    public function addDeliveryAddress(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');

            return  $this->deliveryAddressActivity->addNewDeliveryAddress($request->post(),$accessToken,$sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function editDeliveryAddress(Request $request,$id)
    {
        try
        {
            return  $this->deliveryAddressActivity->updateDeliveryAddress($request->post(),$id);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }
    

    public function viewDeliveryAddress(Request $request,$id)
    {
        try
        {
            return  $this->deliveryAddressActivity->viewDeliveryAddress($request->post(),$id);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function removeDeliveryAddress(Request $request)
    {
        try
        {
            return  $this->deliveryAddressActivity->deleteDeliveryAddress($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listDeliveryAddress(Request $request){

        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');

            return  $this->deliveryAddressActivity->getDeliveryAddressList($accessToken,$sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function viewProfile(Request $request){

        try
        {
            return  $this->deliveryAddressActivity->getProfile($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function setDefaultDeliveryAddress(Request $request)
    {
        try
        {
            return  $this->deliveryAddressActivity->setDefaultDeliveryAddress($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

}
