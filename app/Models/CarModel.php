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


class CarModel extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "parts.model";
    public $timestamps = false;

    public function carMake()
    {
    	return $this->belongsTo('App\Models\CarMake', 'make_id');
    }
    public function carYear()
    {
    	return $this->belongsTo('App\Models\CarYear', 'year_id');
        
    }

    public function carModelInfo()
    {
    	return $this->hasMany('App\Models\CarModel', 'model_id');
    }

    public function carYearName()
    {
    	return $this->belongsTo('App\Models\CarYear', 'year_id');
    }

    public function autoPart()
    {
    	return $this->belongsTo('App\Models\AutoPart', 'auto_part_id');
    }


    public function getDescriptionForEvent(string $eventName): string
    {
        return "The CarModel ".$this->name." has been {$eventName}:{$eventName}";
    }

}
