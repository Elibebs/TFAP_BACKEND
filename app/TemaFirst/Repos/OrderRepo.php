<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\Permission;
use App\TemaFirst\Utilities\Generators;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\TemaFirst\Utilities\Constants;
use App\Mail\WelcomeMail;
use App\Notifications\WelcomeUser;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\AutoPart;
use App\Models\PartImage;
use App\Models\Address;
use App\Models\PaymentInfo;
use App\Activity;
use Illuminate\Notifications\Notifiable;
use App\TemaFirst\Repos\AutoPartsRepo;
use Illuminate\Support\Facades\DB;


use Excel;
use Redirect;
use Session;


class OrderRepo extends AuthRepo
{
    use Notifiable;

    protected $autoPartRepo;

	public function __construct(Order $order, AutoPartsRepo $autoPartRepo)
    {
        $this->model = $order;
        $this->autoPartRepo = $autoPartRepo;

    }


    public function addOrder(Array $data, $cart)
    {
        //$orderItems = $this->addItems($orderItems->$id);

        $order = new Order;
        $order->customer_id = $data['customer_id'];
        $order->shipping_id = $data['shipping_id'];
        $order->UID =  Generators::generateUniq();
        $order->status = Constants::ORDER_PENDING;
        $order->reference_number =  Generators::generateOrderNumber();
    	$order->created_at = Carbon::now();
        $order->updated_at = Carbon::now();

    	if($order->save())
    	{
            $this->addItems($order,$cart);
            return $order;
    	}
    	return null;
    }

    public function addItems($order, $cart){
        $cart_items = $cart->cartItems;

        foreach($cart_items as $cart_item){
            
            $auto_part = $this->autoPartRepo->getAutoPartById($cart_item->part_id);
            
            if($auto_part){

                $orderItem = new OrderItem;
                $orderItem->part_name = $auto_part->name;
                $orderItem->part_id = $auto_part->id;
                $orderItem->order_id = $order->id;
                $orderItem->unit_price = $cart_item->unit_price;
                $orderItem->quantity = $cart_item->quantity;
                $orderItem->created_at = Carbon::now();
    
                if($orderItem->save()){
                    $cart_item->status = Constants::ORDER_CHECKEDOUT;
                    $cart_item->save();
                }
            }

        }

    }

    public function searchOrders(Array $data){
        $customer_name=$data['customer_name']??null;
        $shipping=$data['shipping']??null;
        $status=$data['status']??null;
        $reference_number=$data['reference_number']??null;
        $created_at=$data['created_at']??null;
        $search=$data['search']??null;

        $query= Order::query();

        if(isset($status)){
            $query->where('status', 'ilike', '%'.$status.'%');
        }

        if(isset($reference_number)){
            $query->where('reference_number', 'ilike', '%'.$reference_number.'%');
        }

        if(isset($created_at)){
            $query->where('created_at', 'ilike', '%'.$created_at.'%');
        }


        if(isset($search)){
            $query->whereHas('customer', function ($query) use ($search){
                        $query->where('name', 'ilike', '%'.$search.'%');})
                      ->orWhereHas('shipping', function ($query) use ($search){
                        $query->where('address', 'ilike', '%'.$search.'%');})
                     ->orWhere('status', 'ilike', '%'.$search.'%')
                     ->orWhere('reference_number', 'ilike', '%'.$search.'%') 
                     ->orWhere('created_at', 'ilike', '%'.$search.'%');
        //    });
        }

        if(isset($customer_name)){
            $query->whereHas('customer', function ($query) use ($customer_name){
                $query->where('name', 'ilike', '%' . $customer_name .'%');
            });
        }

        if(isset($shipping)){
            $query->whereHas('shipping', function ($query) use ($shipping){
                $query->where('address', 'ilike', '%' . $shipping .'%');
            });
        }



        $orders = $query->orderBy('customer_id', 'asc')->with(['customer' => function ($q) {
            $q->select('user_id','name');
        }])->with(['shipping' => function ($q) {
            $q->select('id','address');
        }])->get();
         foreach($orders as $order){
            unset($order->customer_id);
            //unset($order->device_id);
         }
    
            return $orders;
    }



    public function getAllCartItems(){
        return CartItem::paginate(20);
    }



    public function listOrders($filters){
        $pageSize = $filters['pageSize'] ?? 15;
        $predicate = Order::query();
        foreach ($filters as $key => $filter) {
            if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
            {
                continue;
            }
    
            $predicate->where($key, $filter);
         }

        $orders = $predicate->with(['customer' => function ($q) {
            $q->select('user_id','name');
        }])->with('shipping')->paginate($pageSize);
        
        // foreach($orders as $key => $cart){
        //     $orders[$key]['total_amount'] = $cart->totalAmount();
        // }

        return $orders;
    }



    public function viewOrders($id){
        $orders = Order::with('shipping')->where("customer_id", $id)->get();

        foreach($orders as $key => $order){
            $orders[$key]['total_amount'] = $order->totalAmount(); 
            Log:info($order->totalAmount());

            if(count($orders[$key]->itemsInOrder) > 0){
                foreach($orders[$key]->itemsInOrder as $Item){
                    $partImage = PartImage::where('part_id',$Item->part_id)->first();
                    Log::info($partImage);
                    if($partImage != null){
                        $Item['image_url'] = url("system/autopart/image/{$partImage->name}");
                    }
                    else{
                        $Item['image_url'] = null;
                    }  
    
                    $Item['part'] = AutoPart::find($Item->part_id);
                     unset($Item['part']->description);
                     unset($Item['part']->subcategory_id);
                     unset($Item['part']->model_id);
                     unset($Item['part']->unit_price);
                     unset($Item['part']->fit_note);
                     unset($Item['part']->condition);
                     unset($Item['part']->quantity);
                     unset($Item['part']->universal_part);
                     unset($Item['part']->created_at);
                     unset($Item['part']->updated_at);
                     unset($Item['part']->status);
                     unset($Item['part']->seller_id);
                }
            }
            
      
            // $carts[$key]['cartItemInfo'] = CartItem::with(['part' => function ($q) {
            //     $q->select('id','name');
            // }])->get();
          //  $carts[$key]['part'] = CartItem::find($carts[$key]->cartItems->id);
        }

        return $orders;
    }


    public function deleteCartItem($id){

        $cartItem = CartItem::where('id',$id)->first();
        return $cartItem->delete();
    }

public function getShippingInfoById($id)
{
    return Address::where("id", $id)->first();
}

public function getCustomerById($user_id)
{
    return User::where("user_id", $user_id)->first();
}

public function getAddressById($id)
{
    return Address::where("id", $id)->first();
}

public function getPaymentMadeOrdersGroup($filters)
{
    $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_DAY;

    $predicate = Order::query();

    if($filter_date == Constants::FILTER_DATE_MONTH){
       return $predicate->get()
        ->groupBy(function($results){
            return Carbon::parse($results->created_at)->format('W');
        });
    }else if($filter_date== Constants::FILTER_DATE_WEEK){
        $predicate->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())
        ->get()
        ->groupBy(function ($results){
            return Carbon::parse($results->created_at)->format('d');
        });
    }else{
        return $predicate->whereDate('created_at', Carbon::today())->get();
    }

}

/**
 * Delete group by code commented if current implementation is approved
 */
public function getPaymentMadeOrdersByWeekMonth($filters)
{
    $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_WEEK;
    $predicate = Order::query();

    if($filter_date == Constants::FILTER_DATE_MONTH){
       return $predicate->whereMonth('created_at',Carbon::now()->month)
                ->orderBy('created_at','ASC')
                ->get();
                // ->groupBy(function($var){
                //     return Carbon::parse($var->created_at)->format('W');
                // });
    }else if($filter_date== Constants::FILTER_DATE_WEEK){
       return $predicate->where('created_at', '>=', Carbon::now()->startOfWeek())
        ->where('created_at', '<=', Carbon::now()->endOfWeek())
        ->orderBy('created_at','ASC')
        ->get();
        // ->groupBy(function ($results){
        //     return Carbon::parse($results->created_at)->format('d');
        // });
    }else{
       return $predicate->whereDate('created_at', Carbon::today());
    }
}

/**
 * Delete group by code commented if current implementation is approved
 */

public function getPaymentMadeOrdersByWeekMonthPrevious($filters)
{
    $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_WEEK;
    $predicate = Order::query();

    if($filter_date == Constants::FILTER_DATE_MONTH){
       return $predicate->whereMonth('created_at',Carbon::now()->subMonth()->month)
                ->orderBy('created_at','ASC')
                ->get();
                // ->groupBy(function($var){
                //     return Carbon::parse($var->created_at)->format('W');
                // });
    }else if($filter_date== Constants::FILTER_DATE_WEEK){
        $previous_week = strtotime("-1 week +1 day");
        $start_week = strtotime("last sunday midnight",$previous_week);
        $end_week = strtotime("next saturday",$start_week);
        $start_week = date("Y-m-d",$start_week);
        $end_week = date("Y-m-d",$end_week);

       return $predicate->whereBetween('created_at',[$start_week, $end_week])
       ->orderBy('created_at','ASC')
        ->get();
        // ->groupBy(function ($results){
        //     return Carbon::parse($results->created_at)->format('d');
        // });
    }else{
       return $predicate->whereDate('created_at', Carbon::today());
    }
}

}