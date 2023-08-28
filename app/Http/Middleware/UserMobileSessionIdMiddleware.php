<?php

namespace App\Http\Middleware;

use Closure;
use App\TemaFirst\Repos\UserRepo; 
use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;

class UserMobileSessionIdMiddleware
{
    protected $apiResponse;
    protected $userRepo;

    public function __construct(ApiResponse $apiResponse, UserRepo $userRepo)
    {
        $this->apiResponse = $apiResponse;
        $this->userRepo = $userRepo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $sessionId = $request->headers->get('session-id');

        if($sessionId === null) {
            Log::warning("No session-id header values present, throwing forbidden...");
            // Throw Forbidden
            return $this->apiResponse->forbidden("session-id is unauthorized");
        }

        if(!$this->userRepo->isSessionIdValid($sessionId)) {
            Log::warning("session-id is invalid...");
            // Throw Forbidden
            return $this->apiResponse->forbidden("session-id is unauthorized");
        }

        return $next($request);
    }
}
