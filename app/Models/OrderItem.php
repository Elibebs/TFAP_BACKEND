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


class OrderItem extends Model
{
    use Notifiable;
    use LogsActivity;

	protected $primaryKey = "id";
    protected $table = "activities.order_items";
    public $timestamps = false;


    public function order()
    {
    	return $this->belongsTo('App\Models\OrderItem', 'order_id');
    }

    public function orderItems()
    {
    	return $this->belongsTo('App\Models\OrderItem', 'user_id');
    }

    public function items()
    {
    	return $this->hasMany('App\Models\OrderItem', 'user_id');
    }

    public function partImage()
    {
    	return $this->hasOne('App\Models\PartImage', 'part_id');
    }

}
