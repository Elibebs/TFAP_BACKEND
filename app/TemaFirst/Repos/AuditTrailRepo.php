<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\Permission;
use App\Models\SystemUser;
use App\TemaFirst\Utilities\Generators;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\TemaFirst\Utilities\Constants;
use App\Mail\WelcomeMail;
use App\Notifications\WelcomeUser;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Activity;
use App\Exports\SystemUsersExport;
use App\Exports\ExportAuditTrail;
use App\Exports\RolesExport;
use Illuminate\Notifications\Notifiable;

    
use Excel;
use Redirect;
use Session;

class AuditTrailRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(SystemUser $systemuser)
    {
        $this->model = $systemuser;
    }

public function searchSystemUserAuditTrail(Array $data){
        $log_name=$data['log_name']??null;
        $description=$data['description']??null;
        $subject_type=$data['subject_type']??null;
        $properties=$data['properties']??null;
        $created_at=$data['created_at']??null;
        $updated_at=$data['updated_at']??null;
        $search=$data['search']??null;

        $query= Activity::query();

        if(isset($log_name)){
            $query->where('log_name', 'ilike', '%'.$log_name.'%');
        }
        if(isset($description)){
            $query->where('description', 'ilike', '%'.$description.'%');
        }
        if(isset($subject_type)){
            $query->where('subject_type', 'ilike', '%'.$subject_type.'%');
        }
        if(isset($properties)){
            $query->where('properties', 'ilike', '%'.$properties.'%');
        }
        if(isset($created_at)){
            $query->where('created_at', 'ilike', '%'.$created_at.'%');
        }
        if(isset($updated_at)){
            $query->where('updated_at', 'ilike', '%'.$updated_at.'%');
        }

        if(isset($search)){
                   $query->where('log_name', 'ilike', '%'.$search.'%')
                 ->orWhere('description', 'ilike', '%'.$search.'%')
                 ->orWhere('subject_type', 'ilike', '%'.$search.'%')
                 ->orWhere('properties', 'ilike', '%'.$search.'%')
                 ->orWhere('created_at', 'ilike', '%'.$search.'%')
                 ->orWhere('updated_at', 'ilike', '%'.$search.'%');
        }


        $activities = $query->orderBy('log_name', 'asc')->get();


            return $activities;
    
    }
public function systemUsersAuditTrailExporter($systemuser)
{
    return Excel::download(new ExportAuditTrail, 'systemusersaudittrails.xlsx');

}
public function getAllAuditTrails($filters){
  
    $pageSize = $filters['pageSize'] ?? 18;
    $predicate = Activity::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
     }

    $activities= $predicate->orderBy('created_at', 'DESC')->paginate($pageSize);

    foreach ($activities as $activity) {

        //Log::info($activity);
        $activity['system_user'] = $activity->causer != null ? $activity->causer->name : null ;

        unset($activity->causer);

    }

    return $activities;
    
}
public function getWhereAccessTokenAndSessionId(String $accessToken, String $sessionId)
{
    $entity = $this->model->where("access_token", $accessToken)->where("session_id", $sessionId)->first();
    
    return $entity;
}

}