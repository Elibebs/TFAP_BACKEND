<?php

namespace App\Http\Middleware;

use Closure;
use App\TemaFirst\Repos\SystemUserRepo; 
use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;

class UserSessionIdMiddleware
{
    protected $apiResponse;
    protected $systemUserRepo;

    public function __construct(ApiResponse $apiResponse, SystemUserRepo $systemUserRepo)
    {
        $this->apiResponse = $apiResponse;
        $this->systemUserRepo = $systemUserRepo;
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

        if(!$this->systemUserRepo->isSessionIdValid($sessionId)) {
            Log::warning("session-id is invalid...");
            // Throw Forbidden
            return $this->apiResponse->forbidden("session-id is unauthorized");
        }

        return $next($request);
    }
}
