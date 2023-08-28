<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPassword as ResetPasswordNotification;



class Role extends \Spatie\Permission\Models\Role
{
    use HasRoles;
    use Notifiable;
    use LogsActivity;
  //  use SoftDeletes;

    protected $primaryKey = "id";
    protected $table = "roles";
    protected $dateFormat = 'Y-m-d H:i:s';

    protected static $logAttributes = ['id','name','created_at'];
    protected static $logName = 'system user role';



    public function getDescriptionForEvent(string $eventName): string
    {
        return "The role ".$this->name." on ".$this->created_at."  has been {$eventName}:{$eventName}";
    }

    public function systemUser()
    {
    	return $this->belongsTo('App\Models\SystemUser', 'role_id');
    }

}
