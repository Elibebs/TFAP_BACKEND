<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\TemaFirst\Activities\AuthActivity;
use Illuminate\Http\Request;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Api\ApiResponse;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    protected $authActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        AuthActivity $authActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->authActivity = $authActivity;
    }

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

	public function loginUser(Request $request)
	{
		try
		{
            return  $this->authActivity->freeLoginUser($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }
    
    public function logoutUser(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            return  $this->authActivity->logoutUser($accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }


}
