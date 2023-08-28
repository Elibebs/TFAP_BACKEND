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


class CarMake extends Model
{
    use Notifiable;

	protected $primaryKey = "id";
    protected $table = "activities.make";
    public $timestamps = false;

    public function carModels()
    {
    	return $this->hasMany('App\Models\CarModel', 'make_id');
    }
}
