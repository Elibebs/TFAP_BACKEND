<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\DeviceRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\DeviceTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;



class DeviceActivity extends BaseActivity
{
	use DeviceTrait;

    protected $deviceRepo;
	protected $apiResponse;

	public function __construct(
        DeviceRepo $deviceRepo,
		ApiResponse $apiResponse
	)
    {
        $this->deviceRepo = $deviceRepo;
		$this->apiResponse = $apiResponse;
    }

    public function getDevice(Request $request)
    {
            $identifier = $request->headers->get('identifier');

            if($identifier === null) {
                Log::warning("No Identifier header values present, throwing forbidden identifier not found...");
                // Throw Forbidden
                return $this->apiResponse->notFoundError("identifier is not found");
            }

            $device = $this->deviceRepo->getDevice($identifier);
            if(!isset($device))
            {
                $message = "Device with {$identifier} not found";
                ErrorEvents::apiErrorOccurred($message);
                return $this->apiResponse->notFoundError($message);
            }

            $message = "Device retrieved successfully.";
            return $this->apiResponse->success($message, ["data" => $device] );

    }


}