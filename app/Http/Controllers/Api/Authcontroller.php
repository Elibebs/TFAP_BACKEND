<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\TemaFirst\Activities\AuthActivity;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;

class Authcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
	public function loginUser(Request $request)
	{
		try
		{
            return  $this->authActivity->attemptSystemUserLogin($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function userRegister(Request $request)
    {
        try
        {
            Log::notice($request->post());
            return  $this->authActivity->userRegister($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyUserPin(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            Log::notice("verifyUserPin pin is {$request->post('pin')}");
            return  $this->authActivity->verifyUserPin($request->post(), $accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function userProfile(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            return  $this->authActivity->getUserProfile($accessToken, $sessionId);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }


    public function updateUserProfile(Request $request)
    {
        try
        {
            $accessToken = $request->header('access-token');
            $sessionId = $request->header('session-id');
            Log::notice("logging...image");
            Log::notice($request->post());
            return  $this->authActivity->updateUserProfile($request->post(), $accessToken, $sessionId);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
