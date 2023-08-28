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


class Order extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "activities.order";
    public $timestamps = false;



    public function getDescriptionForEvent(string $eventName): string
    {
        return "The Order  with reference number".$this->reference_number." has been {$eventName}:{$eventName}";
    }


    public function shippingInfo()
    {
    	return $this->hasMany('App\Models\ShippingInfo', 'shipping_id');
    }

        public function itemsInOrder()
    {
    	return $this->hasMany('App\Models\OrderItem', 'order_id');
    }

    public function shipping()
    {
    	return $this->belongsTo('App\Models\ShippingInfo', 'shipping_id');
    }

    public function orderItems()
    {
    	return $this->hasMany('App\Models\OrderItem', 'item_id');
    }

    public function items()
    {
    	return $this->belongsTo('App\Models\OrderItem', 'order_id');
    }

    public function autoPart()
    {
    	return $this->hasMany('App\Models\AutoPart', 'part_id');
    }

    public function partImage()
    {
    	return $this->hasMany('App\Models\PartImage', 'part_id');
    }

    public function paymentInfo()
    {
    	return $this->hasMany('App\Models\PaymentInfo', 'payment_id');
    }

    public function customer()
    {
    	return $this->belongsTo('App\Models\User', 'customer_id');
    }
    // public function totalAmount()
    // {
    //     $total =$this->cartItems->sum(function($cartItem){
    //         return $cartItem->unit_price * $cartItem->quantity;
    //     });

    //     return $total;
    // }

    public function totalAmount()
    {
        $total = 0;
        foreach($this->itemsInOrder as $item){
            $total += $item->unit_price * $item->quantity;
        }
        return $total;
    }

    public function totalItems()
    {
        $total = 0;
        foreach($this->itemsInOrder as $item){
            $total += $item->quantity;
        }
        return $total;
    }

}
