<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\CarModelActivity;

use Redirect;
use Session;
use Excel;

class CarModelController extends Controller
{

    protected $carModelActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        CarModelActivity $carModelActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->carModelActivity = $carModelActivity;
    }

    public function addCarModel(Request $request)
    {
        try
        {
            return  $this->carModelActivity->addNewCarModel($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function editCarModel(Request $request)
    {
        try
        {
            return  $this->carModelActivity->updateCarModel($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteCarModel(Request $request)
    {
        try
        {
            return  $this->carModelActivity->removeCarModel($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listCarModel(Request $request){

        try
        {
            return  $this->carModelActivity->getCarModel($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }


    public function searchMakeModelYear(Request $request){

        try
        {
            return  $this->carModelActivity->searchMakeModelYear($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

}
