<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\CartActivity;

use Redirect;
use Session;
use Excel;

class CartController extends Controller
{

    protected $cartActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        CartActivity $cartActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->cartActivity = $cartActivity;
    }

    public function addCart(Request $request)
    {
        try
        {
            return  $this->cartActivity->addNewCartItem($request, $request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function removeCartItem(Request $request)
    {
        try
        {
            return  $this->cartActivity->deleteCartItem($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function mViewCart(Request $request){

        try
        {
            return  $this->cartActivity->mGetCart($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function searchCarts(Request $request)
    {
        try
        {
            return  $this->cartActivity->searchCarts($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function viewUserCartItems(Request $request){

        try
        {
            return  $this->cartActivity->viewUserCartItems($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }


    public function listCartItem(Request $request){

        try
        {
            return  $this->cartActivity->getCartItemsList($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function deleteCart(Request $request)
    {
        try
        {
            return  $this->cartActivity->removeCart($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }



}
