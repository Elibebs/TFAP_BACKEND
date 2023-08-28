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


class RoleRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(Role $role)
    {
        $this->model = $role;
    }

    
    
    public function updateSystemUserRole(Array $data, $id){
       // Log::notice("role update called");
        $nameRule = array(
            'name'=>'required|min:2',
        );

        $permRule = array(
            'permissions' =>'required',
        );
        

        $role = Role::where("id", $id)->first();

        $role->name = $data['name'];
        
        $permissions = $data['permissions'];
        $permissions = explode(",", $permissions);

        Log::info($permissions);

        $p_all = Permission::all();//Get all permissions

        foreach ($p_all as $p) {
            $role->revokePermissionTo($p); //Remove all permissions associated with role
        }
        //Looping thru selected permissions
        foreach ($permissions as $permission) {
            $p = Permission::where('id', '=', $permission)->first();

            if($p){
         //Fetch the newly updated role and assign permission
               Log::info($p);
                $role->givePermissionTo($p);
            }

        }

         if($role->update())
            return $role;
                else
            return null;
    }
    
    public function searchSystemUserRole(Array $data){
        $name=$data['name']??null;
        $search=$data['search']??null;

        $query= Role::query();

        if(isset($name)){
            $query->where('name', 'ilike', '%'.$name.'%');
        }

        if(isset($search)){
                $query->where('name', 'ilike', '%'.$search.'%');
                    // ->orWhere('email', 'ilike', '%'.$search.'%');
        }


        $roles = $query->orderBy('name', 'asc')->get();

        foreach ($roles as $role) {

            Log::info($role);
            $role['user_number'] = SystemUser::role($role)->count();

        }


            return $roles;
    }
    

    public function getRoleById($id)
    {
        return Role::where("id", $id)->first();
    }
    public function getPermsById($id)
    {
        return Permission::where("id", $id)->first();
    }


    public function getSuperAdminRole(){
        return Role::where('name','SuperAdmin')->first();
    }


    public function SystemUserRoleDelete($id)
    {
        $role = Role::where("id", $id)->first();
        return $role->delete();
    }
public function systemUsersRolesExporter($systemuser)
{
    return Excel::download(new RolesExport, 'systemusersroles.xlsx');

}
public function createSystemuserRole(Array $data){
    Log::notice("role store called");
    $arrStr=serialize($data['permissions']);
    Log::notice("arr=".$arrStr);
    //Validate name and permissions field
    $rules = array(
        'name'=>'required|unique:roles|min:2',
        'permissions' =>'required',
    );
  
    $role = new Role();
    $role->name = $data['name'];
    $role->created_at = Carbon::now();
    $role->updated_at = Carbon::now();
    // $role->name = $name;


    $permissions = $data['permissions'];
    $permissions = explode(",", $permissions);
    //Looping thru selected permissions
    foreach ($permissions as $permission) {
        $p = Permission::where('id', '=', $permission)->firstOrFail();
    //Fetch the newly created role and assign permission
        $role->givePermissionTo($p);
    }
        if($role->save())
    	{
    		return $role;
    	}
    	return null;
}

    public function getAllRoles($filters)
    {
        $pageSize = $filters['pageSize'] ?? 18;
        $predicate = Role::query();
        foreach ($filters as $key => $filter) {
            if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
            {
                continue;
            }
    
            $predicate->where($key, $filter);
         }

         $roles = Role::paginate($pageSize);

        foreach ($roles as $role) {

            Log::info($role);
            $role['user_number'] = SystemUser::role($role)->count();

        }
   
        return $roles;
        
    }

public function roleExists($name)
{
    $role = Role::where('name',$name)->first();
    if($role)
        return true;
    else
        return false;
}

public function getWhereAccessTokenAndSessionId(String $accessToken, String $sessionId)
{
    $entity = $this->model->where("access_token", $accessToken)->where("session_id", $sessionId)->first();
    
    return $entity;
}

public function viewRoleDetails($data, $id){
    $role = Role::where("id", $id)->first();

    //Log::info($roles);
    if($role != null){
        $role['SystemUser'] = SystemUser::role($role)->select('name','email','status')->get();
        $role->permissions;
     

        unset($role->guard_name);
        unset($role->created_at);
        unset($role->updated_at);
        return $role;
    }
       return null;
    }

    public function getAllPermissions()
    {
        return Permission::get();

    }

}