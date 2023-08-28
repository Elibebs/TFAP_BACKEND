<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserWallet;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Utilities\Generators;
use Illuminate\Support\Facades\Log;
use App\Models\SystemImage as Image;

class UserRepo extends AuthRepo
{

	public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function freeLogin(Array $data)
    {
    	$entity = $this->model->where("phone_number", $data['phone_number'])->first();

		if(isset($entity)) {
            $entity->access_token = $entity->access_token != null ? $entity->access_token : Generators::generateAccessToken();
        	$entity->session_id = $entity->session_id != null ? $entity->session_id : Generators::generateSessionId();
			$entity->session_id_time = date('Y-m-d H:i:s',strtotime("+".env('SESSION_ID_LIFETIME_DAYS', 30)." days",time()));
            $entity->last_logged_in = date("Y-m-d H:i:s");
            $entity->player_id = $data['player_id'] ?? null;
            $entity->verified=false;
			if($entity->update())
			{
                if(isset($entity)) {

                   if(isset($entity->image)){
                        $entity['image_url'] = url("/api/user/image/". $entity->image->name);
                   }else{
                    $entity['image_url']  = null;
                   }
                   
                   unset($entity['image']);
                }
			    return $entity;
			}
		}

		return null;
    }

    public function getUserById($user_id)
    {
        return User::where("user_id", $user_id)->first();
    }

    public function getUserByPhoneNumber($phone_number){
        return User::where('phone_number',$phone_number)->first();
    }

    public function getUserByEmail($email){
        return User::where('email',$email)->first();
    }

    public function registerFreeUser(Array $data){
        $user = new User;

    	$user->user_uniq = Generators::generateUniq();
    	$user->phone_number = $data['phone_number'];
        $user->name = $data['name'];
        $user->type = $data['account_type'];
        $user->email = $data['email'] ?? null;
        $user->player_id = $data['player_id'] ?? null;
        $user->verified = false;
        $user->status = Constants::STATUS_ENABLED;
    	$user->access_token = null;
		$user->session_id = null;
		$user->session_id_time = null;
		$user->last_logged_in = null;
    	$user->created_at = Carbon::now();
    	$user->updated_at = Carbon::now();

    	if($user->save())
    	{
    		return $user;
    	}
    	return null;
    }


    public function updateUserProfile(Array $data, $userId)
    {
        $user = User::where("user_id", $userId)->first();

        if(isset($data['name'])) { $user->name = $data['name']; }
        if(isset($data['phone_number'])) {
            if($user->phone_number != $data['phone_number']){
                $user->phone_number = $data['phone_number'];
                $user->verified = false;
            }
        }
        if(isset($data['email'])) { $user->email = $data['email']; }
        if(isset($data['address'])) { $user->address = $data['address']; }
        if(isset($data['account_type'])){$user->type = $data['account_type'];}
        return $user->save();
    }



    public function setUserVerified($userId)
    {
        $user = User::where("user_id", $userId)->first();

        if(!isset($user))
        {
            return false;
        }

        $user->verified = true;
        return $user->update();
    }

    public function getAllCustomers($filter)
    {
        return User::get();
    }

    public function getUsersGroup($filters)
    {
        $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_DAY;

        $predicate = User::query();

        if($filter_date == Constants::FILTER_DATE_MONTH){
        return $predicate->get()
            ->groupBy(function($val){
                return Carbon::parse($val->created_at)->format('W');
            });
        }else if($filter_date== Constants::FILTER_DATE_WEEK){
            $predicate->where('created_at', '>', Carbon::now()->startOfWeek())
            ->where('created_at', '<', Carbon::now()->endOfWeek())
            ->get()
            ->groupBy(function ($val){
                return Carbon::parse($val->created_at)->format('d');
            });
        }else{
            return $predicate->whereDate('created_at', Carbon::today())->get();
        }

    }

    public function getUsersGroupPreviousCount($filters)
    {
        $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_DAY;

        $predicate = User::query();

        if($filter_date == Constants::FILTER_DATE_MONTH){
            return $predicate->count();
        }else if($filter_date== Constants::FILTER_DATE_WEEK){
            return $predicate->where('created_at', '>', Carbon::now()->startOfWeek())
            ->where('created_at', '<', Carbon::now()->endOfWeek())
            ->count();
        }else{
            return $predicate->whereDate('created_at', Carbon::today())->count();
        }

    }
}
