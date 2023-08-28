<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;

class SystemUser extends Authenticatable
{
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $primaryKey = "id";
    protected $table = "system.users";
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected static $logAttributes = ['id','name'];
    protected static $logName = 'system user';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','email', 'password',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($password){
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function groups()
    {
    	return $this->belongsToMany('App\Models\Group', 'system.group_users', 'id', 'group_id');
    }

    public function image(){
       return $this->hasOne('App\Models\SystemUserImage', 'system_user_id');
    }

    public function notes()
    {
        return $this->morphMany('App\Models\Note', 'notable');
    }

    public function sendPasswordResetNotification($token)
    {
        // Your your own implementation.
        $this->notify(new ResetPasswordNotification($token));
    }
    //     public function country()
    // {
    // 	return $this->belongsTo('App\Models\Country', 'country_id');
    // }
}
