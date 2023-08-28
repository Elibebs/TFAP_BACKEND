<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;


class AutoPart extends Model
{
    use Notifiable;

	protected $primaryKey = "id";
    protected $table = "parts.auto_parts";
    public $timestamps = false;

    public function carModel()
    {
    	return $this->hasMany('App\Models\CarModel', 'auto_parts_id');
    }
    public function subCategory()
    {
    	return $this->belongsTo('App\Models\SubCategory', 'subcategory_id');
    }

    public function carModelInfo()
    {
    	return $this->belongsTo('App\Models\CarModel', 'model_id');
    }

    public function specs()
    {
    	return $this->belongsTo('App\Models\Specification', 'auto_part_id');
    }

    public function subCategories()
    {
    	return $this->belongsTo('App\Models\SubCategory', 'subcategory_id');
    }

    public function partImage()
    {
    	return $this->hasMany('App\Models\PartImage', 'part_id');
    }

    public function images()
    {
        return $this->hasMany('App\Models\PartImage','part_id')->select(['id','name']);
    }

    public function Categories()
    {
    	return $this->belongsTo('App\Models\Category', 'category_id');
    }

    //Delete this function if not in use
    public function Specification()
    {
    	return $this->hasMany('App\Models\Specification', 'auto_parts_id');
    }

    
    public function specifications()
    {
        return $this->hasMany('App\Models\Specification','auto_part_id');
    }

    public function autoPartSeller()
    {
    	return $this->hasMany('App\Models\Seller', 'seller_id');
    }
    public function autoImage()
    {
    	return $this->hasMany('App\Models\Image', 'image_id');
    }

    public function  orderItems()
    {
        return $this->hasMany('App\Models\OrderItem','part_id');
    }

    public function quantitySold()
    {
        $order_items = $this->orderItems;
        $quantity_sold = 0;
        foreach($order_items as $order_item)
        {
            $quantity_sold += $order_item->quantity;
        }
        return $quantity_sold;
    }

    public function amountSold()
    {
        $order_items = $this->orderItems;
        $amount_sold = 0;
        foreach($order_items as $order_item)
        {
            $amount_sold += ($order_item->quantity * $order_item->unit_price);
        }
        return $amount_sold;
    }

}
