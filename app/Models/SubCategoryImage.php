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


class SubCategoryImage extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "parts.sub_category_images";
    public $timestamps = false;


    public function subCategoryImage()
    {
    	return $this->belongsTo('App\Models\SubCategoryImage', 'subcategory_id');
    }


    public function getDescriptionForEvent(string $eventName): string
    {
        return "The SubCatogoryImage ".$this->name." has been {$eventName}:{$eventName}";
    }

}
