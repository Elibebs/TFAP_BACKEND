<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\YearActivity;

class YearController extends Controller
{

    protected $yearActivity;
    protected $apiResponse;

	public function __construct(ApiResponse $apiResponse,YearActivity $yearActivity)
    {
        $this->apiResponse = $apiResponse;
    	$this->yearActivity = $yearActivity;
    }

    public function index(Request $request)
    {
        try
        {
            return  $this->yearActivity->getYears();
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function addCarYear(Request $request)
    {
        try
        {
            return  $this->yearActivity->addCarYear($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteCarYear(Request $request)
    {
        try
        {
            return  $this->yearActivity->deleteCarYear($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listCarYears(Request $request)
    {
        try
        {
            return  $this->yearActivity->listCarYears($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

}
