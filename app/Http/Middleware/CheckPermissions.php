<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Repos\SystemUserRepo; 
use App\TemaFirst\Api\ApiResponse;

class CheckPermissions
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
        $sessionId = $request->headers->get('session-id');
        $systemUser = $this->systemUserRepo->getWhereAccessTokenAndSessionId($accessToken,$sessionId);

        if($systemUser === null) {
            return $this->apiResponse->forbidden("user is unauthorized");
        }

        if ($systemUser->hasRole('SuperAdmin')) //If user has this //permission
        {
            return $next($request);
        }

        $routePermsMap=Permission::routePermissionsMap();
        foreach ($routePermsMap as $route => $permission) {
            Log::notice("permissions => ".$permission);
            if(\Route::current()->getName()==$route){
                Log::notice("route => ".$route." --> ".$permission);
                if($systemUser->hasPermissionTo($permission)){
                    Log::notice('user has permission');
                    return $next($request);
                }else {
                    return $this->apiResponse->unauthorizedPerm();
                }
            }
        }

        if(\Route::current()->getName()=="*.store"){
            return $next($request);
        }
        if(\Route::current()->getName()=="*.update"){
            return $next($request);
        }
        return $next($request);
    }
}
