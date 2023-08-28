<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\OrderActivity;

use Redirect;
use Session;
use Excel;

class OrderController extends Controller
{

    protected $orderActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        OrderActivity $orderActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->orderActivity = $orderActivity;
    }

    public function addOrder(Request $request)
    {
        try
        {
            return  $this->orderActivity->addNewOrder($request, $request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    // public function removeCartItem(Request $request)
    // {
    //     try
    //     {
    //         return  $this->cartActivity->deleteCartItem($request->post());
    //     }
    //     catch(\Exception $e)
    //     {
    //         ErrorEvents::ServerErrorOccurred($e);
    //         return $this->apiResponse->serverError();
    //     }

    // }

    // public function viewCartItem(Request $request){

    //     try
    //     {
    //         return  $this->cartActivity->getCartItems($request->post());
    //     }
    //     catch(\Exception $e)
    //     {
    //         ErrorEvents::ServerErrorOccurred($e);
    //         return $this->apiResponse->serverError();
    //     }
        
    // }

    public function searchOrders(Request $request)
    {
        try
        {
            return  $this->orderActivity->searchOrders($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function listOrders(Request $request){

        try
        {
            return  $this->orderActivity->listOrders($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function viewOrders(Request $request){

        try
        {
            return  $this->orderActivity->viewOrders($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }


}
