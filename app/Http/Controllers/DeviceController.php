<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\DeviceActivity;

use Redirect;
use Session;
use Excel;

class DeviceController extends Controller
{

    protected $deviceActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        DeviceActivity $deviceActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->deviceActivity = $deviceActivity;
    }

    public function getPlayerId(Request $request)
    {
        try
        {
            return  $this->deviceActivity->getDevice($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }
}
