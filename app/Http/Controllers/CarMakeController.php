<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\CarMakeActivity;

use Redirect;
use Session;
use Excel;

class CarMakeController extends Controller
{

    protected $carMakeActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        CarMakeActivity $carMakeActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->carMakeActivity = $carMakeActivity;
    }

    public function addCarMake(Request $request)
    {
        try
        {
            return  $this->carMakeActivity->addNewCarMake($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function editCarMake(Request $request)
    {
        try
        {
            return  $this->carMakeActivity->updateCarMake($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteCarMake(Request $request)
    {
        try
        {
            return  $this->carMakeActivity->removeCarMake($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listCarMake(Request $request){

        try
        {
            return  $this->carMakeActivity->getCarMakes($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function listMakeWithModels(Request $request)
    {
        try
        {
            return  $this->carMakeActivity->listMakeWithModels($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

}
