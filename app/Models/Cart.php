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


class Cart extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "activities.cart";
    public $timestamps = false;



    public function getDescriptionForEvent(string $eventName): string
    {
        return "The Item ".$this->items." has been {$eventName}:{$eventName}";
    }


    public function cartItems()
    {
    	return $this->hasMany('App\Models\CartItem', 'cart_id')->where('status','PENDING');
    }

    public function customer()
    {
    	return $this->belongsTo('App\Models\User', 'customer_id');
    }


    public function partImage()
    {
    	return $this->hasMany('App\Models\PartImage', 'part_id');
    }

    
    public function totalAmount()
    {
        $total = 0;
        foreach($this->cartItems as $cart){
            $total += $cart->unit_price * $cart->quantity;
        }
        unset($this->cartItems);


        return $total;
    }

}
