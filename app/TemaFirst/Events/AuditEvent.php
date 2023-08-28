<?php

namespace App\TemaFirst\Events;

use App\Models\SystemUser;
use App\TemaFirst\Utilities\Constants;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuditEvent
{
	public static function logEvent(Request $request, $model, $message)
	{
        $system_user = SystemUser::where("access_token", $request->headers->get('access-token'))->where("session_id", $request->headers->get('session-id'))->first();
        activity()
        ->performedOn($model)
       ->causedBy($system_user)
       ->log($message . " by {$system_user->name}");

	}
}