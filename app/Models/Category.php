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


class Category extends Model
{
    use Notifiable;

	protected $primaryKey = "id";
    protected $table = "parts.category";
    public $timestamps = false;

    public function subCategories()
    {
    	return $this->hasMany('App\Models\SubCategory', 'category_id')->orderBy('name','ASC');
    }

    public function subCategoryItems()
    {
    	return $this->hasMany('App\Models\SubCategory', 'category_id')->take(3);
    }
    
    public function subCategoriesCount()
    {
    	return $this->subCategories()->count();
    }

    public function autoParts()
    {
    	return $this->hasMany('App\Models\AutoPart', 'category_id');
    }
    public function categoryImage()
    {
    	return $this->hasOne('App\Models\CategoryImage', 'category_id');
    }
    
}
