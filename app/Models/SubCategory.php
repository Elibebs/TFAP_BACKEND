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


class SubCategory extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "parts.sub_category";
    public $timestamps = false;

    public function category()
    {
    	return $this->belongsTo('App\Models\Category', 'category_id');
    }
    public function categoryName(){
        return $this->category();
    }
    public function autoParts()
    {
    	return $this->hasMany('App\Models\AutoPart', 'subcategory_id');
    }

    public function partImage(){
        return $this->morphOne('App\Models\Image', 'part_id');
    }


    public function getDescriptionForEvent(string $eventName): string
    {
        return "The category ".$this->name." has been {$eventName}:{$eventName}";
    }

}
