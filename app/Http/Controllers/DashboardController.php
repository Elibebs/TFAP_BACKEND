<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\DashboardActivity;

class DashboardController extends Controller
{

    protected $apiResponse;
    protected $dashboardActivity;

	public function __construct(ApiResponse $apiResponse, DashboardActivity $dashboardActivity)
    {
        $this->apiResponse = $apiResponse;
        $this->dashboardActivity = $dashboardActivity;
    }

    public function getTopStatistics(Request $request)
    {
        try
        {
            return  $this->dashboardActivity->getTopStatistics($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function getTopGrossingItems(Request $request){

        try
        {
            return  $this->dashboardActivity->getTopGrossingItems($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function getRevenueStatistics(Request $request){

        try
        {
            return  $this->dashboardActivity->getRevenueStatistics($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }
}