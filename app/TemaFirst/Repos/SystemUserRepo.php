<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\Permission;
use App\Models\SystemUser;
use App\Models\SystemUserPasswordReset;
use App\Models\SystemUserImage;
use App\Models\User;
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


class SystemUserRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(SystemUser $systemuser)
    {
        $this->model = $systemuser;
    }


    public function login(Array $data)
    {
        $entity = $this->model->where("email", $data['email'])->first();

        // $roleIds=$data['role_id'];
        // $roleIds = explode(",", $roleIds);
        // $role = Role::where('id',$roleid)->first();

        if(isset($entity)) {
            $passed = \Hash::check($data['password'], $entity->password);

            if($passed) {
                if($entity->access_token==null){
                    $entity->access_token = Generators::generateAccessToken();
                }
				if($entity->session_id==null){
                    $entity->session_id = Generators::generateSessionId();
                }
                $entity->session_id_time = date('Y-m-d H:i:s',strtotime("+".env('SESSION_ID_LIFETIME_DAYS', 30)." days",time()));
                $entity->last_logged_in = date("Y-m-d H:i:s");
                if($entity->update())
                {
                    if(isset($entity)) {

                        if(isset($entity->image)){
                             $entity['image_url'] = url("/systemuser/image/". $entity->image->system_user_id);
                        }else{
                         $entity['image_url']  = null;
                        }
                        
                        unset($entity['image']);
                     }
                //    $entity  = SystemUser::whereHas('roles', function ($q) use ($role) {
                //         $q->where('name', $role);
                //     })->get();

                $entity['roles'] = $entity->getRoleNames();

                    return $entity;
                }
            }
        }

        return null;
    }

    public function createSystemUser(Array $data)
    {
    	$systemuser = new SystemUser;

    	//$systemuser->user_uniq = Generators::generateUniq();
    	$systemuser->name = $data['name'];
    	//$systemuser->password = Hash::make($data['password']);
    	$systemuser->email = $data['email'] ?? null;
    	$systemuser->status = Constants::STATUS_ENABLED;
        $systemuser->player_id = $data['player_id'] ?? null;
        $systemuser->phone_number = $data['phone_number'] ?? null;
    	$systemuser->access_token = null;
		$systemuser->session_id = null;
		$systemuser->session_id_time = null;
		$systemuser->last_logged_in = null;
    	$systemuser->created_at = Carbon::now();
        $systemuser->updated_at = Carbon::now();

        $roleIds=$data['role_id'];
        $roleIds = explode(",", $roleIds);

        foreach ($roleIds as $roleid) {
            $role = Role::where('id',$roleid)->first();
            if(isset($role))
                $systemuser->assignRole($role->name);
        }

    	if($systemuser->save())
    	{
            $token = app('auth.password.broker')->createToken($systemuser);
            $systemuser->notify(new WelcomeUser($token));
    		return $systemuser;
    	}
    	return null;
    }

    public function updateSystemUser($data, $id)
    {
        $systemuser = SystemUser::where("id", $id)->first();

    	if(isset($data['name'])){
            $systemuser->name = $data['name'];
        }

        if(isset($data['email'])){
            $systemuser->email = $data['email'];
        }


        if(isset($data['phone_number'])){
            $systemuser->phone_number = $data['phone_number'];
        }

        if(isset($data['role_id'])){
            $roleIds=$data['role_id'];
            $roleIds = explode(",", $roleIds);

            Log::info( $roleIds );

            if(!$systemuser->hasRole('SuperAdmin')){
                foreach ($systemuser->getRoleNames() as $name) {
                    $systemuser->removeRole($name);
                }

                foreach ($roleIds as $roleid) {
                    $role = Role::where('id',$roleid)->first();
                    if(isset($role))
                        $systemuser->assignRole($role->name);
                        Log::info($systemuser->assignRole($role->name));
                }
                // $systemuser['roles'] = $systemuser->getRoleNames();

                // return $systemuser;
                }

                }
                
                $systemuser->updated_at = Carbon::now();

                $image = SystemUserImage::where('system_user_id', $id)->first(); 
                if(isset($data['base64'])) {

                    if(!$image){
                        $image = new SystemUserImage;
                        $image->name =  Generators::generateUniq();
                        $image->system_user_id = $systemuser->id;
                     }
                    
                        $image->base64 = $data['base64'];

                        
                        $image->save();

                      }
                        if($systemuser->update())
                        {
                            $systemuser['image_url'] = $image ? url("/systemuser/image/{$systemuser->id}") : null;
                        }

                        return $systemuser;
    }

        /**
    * Get member by email address
    */
    public function getMemberByEmail($email){
        return SystemUser::where('email','=',$email)->first();
    }

/**
* Get PasswordReset should get the password reset record by email and token
*/
   public function getMemberPasswordReset($token,$email){
        return SystemUserPasswordReset::where([['email','=',$email],['token','=',$token]])->first();
    }

 /**
 *
 * Change password should change the member password now
 */
 public function changeMemberPassword(Array $request_data, SystemUser $systemuser){
    $systemuser->password = $request_data['new_password'];
    if($systemuser->save())
        return $systemuser;
    else
        return null;
}


    public function searchSystemUser(Array $data){
        $name=$data['name']??null;
        $email=$data['email']??null;
        $role=$data['role']??null;
        $status=$data['status']??null;
        $search=$data['search']??null;

        Log::notice('role = '.$role);

        $query= SystemUser::query();

        if(isset($name)){
            $query->where('name', 'ilike', '%'.$name.'%');
        }

        if(isset($email)){
            $query->where('email', 'ilike', '%'.$email.'%');
        }
        if(isset($status)){
            $query->where('status', 'ilike', '%'.$status.'%');
        }

        if(isset($search)){
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere('email', 'ilike', '%'.$search.'%')
                    ->orWhere('status', 'ilike', '%'.$search.'%');
        }


        $systemusers = $query->orderBy('name', 'asc')->get();

        if(isset($name)||isset($email)||isset($status)||isset($role)||isset($search)){
            foreach ($systemusers as $key => $systemuser) {
                $roleNames = $systemuser->getRoleNames();
                $systemuser['role'] = $roleNames->implode(", ");

                if(!empty($role) && !in_array($role, $roleNames->toArray())){
                    unset($systemusers[$key]);
                }
            }
            return $systemusers;
        }

    }
    public function searchCustomer(Array $data){
        $name=$data['name']??null;

        $query= User::query();

        if(isset($name)){
            $query->where('name', 'ilike', '%'.$name.'%');
        }



        $customers = $query->orderBy('name', 'asc')->get();


            return $customers;
    
    }

    public function getSystemUserById($id)
    {
        return SystemUser::where("id", $id)->first();
    }

    public function getCustomerById($user_id)
    {
        return User::where("user_id", $user_id)->first();
    }

    public function getRoleById($id)
    {
        return Role::where("id", $id)->first();
    }

    public function changeSystemUserStatus($id)
    {
        $systemuser = SystemUser::where("id", $id)->first();

        if(!isset($systemuser)){
            return back()->withErrors(['error' => 'SystemUser cannot be found']);
        }

        if($systemuser->status == Constants::STATUS_ENABLED){
            $systemuser->status = Constants::STATUS_DISABLED;
        }else{
            $systemuser->status = Constants::STATUS_ENABLED;
        }

        return $systemuser->save()? back()
        ->with(['success' => $systemuser->name." has been ".$systemuser->status]):
        back()->withErrors(['email' => trans($response)]);
    }

    public function changeUserStatus($user_id)
    {
        $customer = User::where("user_id", $user_id)->first();

        if(!isset($customer)){
            return back()->withErrors(['error' => 'User cannot be found']);
        }

        if($customer->status == Constants::STATUS_ENABLED){
            $customer->status = Constants::STATUS_DISABLED;
        }else{
            $customer->status = Constants::STATUS_ENABLED;
        }

        return $customer->save()? back()
        ->with(['success' => $customer->name." has been ".$customer->status]):
        back()->withErrors(['email' => trans($response)]);
    }



    public function getDisabledUserById($id)
    {
        return SystemUser::where("id", $id)->first();
    }

    public function getAllSystemUsers($filters){
        $pageSize = $filters['pageSize'] ?? 17;
        $predicate = SystemUser::query();
        foreach ($filters as $key => $filter) {
            if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
            {
                continue;
            }
    
            $predicate->where($key, $filter);
         }
        $system_users = $predicate->paginate($pageSize);

        foreach ($system_users as  $system_user) {

            $system_user['roles'] = $system_user->getRoleNames();

            }
            return  $system_users;

    }

    public function getAllCustomers(){
        return User::paginate(20);
    }


    public function systemUsersExporter($systemuser)
{
    return Excel::download(new SystemUsersExport, 'systemusers.xlsx');

}





public function systemUserPasswordReset($data, $id)
{
     $systemuser = SystemUser::where("id", $id)->first();
    
    $password_reset = new SystemUserPasswordReset();

    $password_reset->email = $systemuser->email;
    $password_reset->token = Generators::generateRandomUniqHash();
    $password_reset->created_at = Carbon::now()->addHour();

    if($password_reset->save())
        return $password_reset;
    else
        return null;
}

public function getUserProfileById($user_id)
{
    return User::where("user_id", $user_id)->first();
}


public function systemUserEmailExists($email)
{
    $systemuser = SystemUser::where('email',$email)->first();
    if($systemuser)
        return true;
    else
        return false;
}


public function getWhereAccessTokenAndSessionId(String $accessToken, String $sessionId)
{
    $entity = $this->model->where("access_token", $accessToken)->where("session_id", $sessionId)->first();
    
    return $entity;
}

public function deleteUser($id)
{
    $user = SystemUser::where('id',$id)->first();
    return $user->delete();
}

public function customerType(){

    $customers = User::select('user_id','type')->where('type', "CAR OWNER")->count();

    $total = User::count();

    $customer_percentage = $customers / $total * 100;


    $auto_mechanics = User::select('user_id','type')->where('type', "AUTO MECHANIC")->count();

    $auto_mechanics_percentage = $auto_mechanics / $total * 100;

     
    return ["car_owners_percentage" => $customer_percentage, 
            "total_car_owners" =>$customers, 
             "auto_mechanics_percentage" => $auto_mechanics_percentage,
             "total_auto_mechanics" =>   $auto_mechanics ];
}


}
