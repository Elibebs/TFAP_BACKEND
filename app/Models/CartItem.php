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


class CartItem extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "activities.cart_items";
    public $timestamps = false;


    public function Cart()
    {
    	return $this->belongsTo('App\Models\CartItem', 'cart_id');
    }


    public function partImage()
    {
    	return $this->hasOne('App\Models\PartImage', 'part_id');
    }


    public function autoPart()
    {
    	return $this->belongsTo('App\Models\AutoPart', 'part_id');
    }

}
