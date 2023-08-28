<?php

namespace App\Http\Middleware;

use Closure;
use App\TemaFirst\Repos\SystemUserRepo; 
use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;

class UserAccessTokenMiddleware
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
        $accessToken = $request->headers->get('access-token');

        if($accessToken === null) {
            Log::warning("No access-token header values present, throwing forbidden...");
            // Throw Forbidden
            return $this->apiResponse->forbidden("access-token is unauthorized");
        }

        if(!$this->systemUserRepo->isAccessTokenValid($accessToken)) {
            Log::warning("access-token is invalid...");
            // Throw Forbidden
            return $this->apiResponse->forbidden("access-token is invalid");
        }

        return $next($request);
    }
}
