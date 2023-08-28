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
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PartImage;
use App\Models\ShippingInfo;
use App\Models\User;
use App\Models\AutoPart;
use App\Activity;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class CartRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(Cart $cart)
    {
        $this->model = $cart;

    }


    public function addCartItem(Array $data, $device_id,$customer_id,$autoPart)
    {
        $cart  = $this->getCart($data,$device_id,$customer_id);

        if(!$cart) return null;

        $cartItem = CartItem::where([['cart_id','=',$cart->id],['part_id','=', $data['part_id']]])->first();
        
        if($cartItem != null){
          $cartItem->quantity = $data['quantity'];
          $cartItem->update();
        }else{
            $cartItem = new CartItem;
            $cartItem->cart_id = $cart->id;
            $cartItem->part_id = $data['part_id'];
            $cartItem->quantity = $data['quantity'];
            $cartItem->unit_price = $autoPart->unit_price;
            $cartItem->status = Constants::STATUS_PENDING;
            $cartItem->created_at = Carbon::now();
            $cartItem->updated_at = Carbon::now();
    
            $cartItem->save();
        }

        
        if($cartItem){

            $partImage = PartImage::where('part_id',$cartItem->part_id)->first();
            Log::info($partImage);
            if($partImage != null){
                $cartItem['image_url'] = url("system/autopart/image/{$partImage->name}");
            }
            else{
                $cartItem['image_url'] = null;
            }  
            
            $cartItem["auto_part_name"] = $cartItem->autoPart->name;
            unset($cartItem["autoPart"]);

            return $cartItem;
        }
        
    	return null;

    }

    public function getCart($data, $device_id, $customer_id){

        $cart = null;
        
        if($customer_id)
        {
            $cart = Cart::where([["customer_id","=",$customer_id],["device_id","=",$device_id]])
                        ->orWhere("customer_id",$customer_id)
                        ->first();
        }else{
            $cart = Cart::where([['device_id','=',$device_id],['customer_id','=', null ]])->first();
            
            if($cart != null &&  $customer_id){
                $cart->customer_id = $customer_id;
                $cart->update();
            }   
        }

        if(!$cart){
            $cart = $this->createCart($data, $device_id,$customer_id);
        }

        return $cart;
    }

    public function createCart($data,$device_id,$customer_id){
        $cart = new Cart;
        $cart->device_id = $device_id;
        $cart->customer_id = $customer_id??null;

        if($cart->save())
            return $cart;
        else
            return null;
    }

    public function searchCarts(Array $data){
        $customer_name=$data['customer_name']??null;
        $search=$data['search']??null;

        $query= Cart::query();


        if(isset($search)){
            $query->whereHas('customer', function ($query) use ($search){
                $query->where('name', 'ilike', '%'.$search.'%');
            });
        }

        if(isset($customer_name)){
            $query->whereHas('customer', function ($query) use ($customer_name){
                $query->where('name', 'ilike', '%' . $customer_name .'%');
            });
        }


        $carts = $query->orderBy('customer_id', 'asc')->with(['customer' => function ($q) {
            $q->select('user_id','name');
        }])->get();
        foreach($carts as $cart){
            Log::info($cart);
            $cart['cart_items'] = CartItem::where('cart_id',$cart->id)->count();
        }
        foreach($carts as $key => $cart){
            $carts[$key]['total_amount'] = $cart->totalAmount(); 
            Log:info($cart->totalAmount());
    
        }
         foreach($carts as $cart){
            unset($cart->customer_id);
            unset($cart->device_id);
         }
    
            return $carts;
    }

    public function getAllCartItems(){
        return CartItem::paginate(20);
    }

    public function getAllCartItemsList($filters){
        $pageSize = $filters['pageSize'] ?? 15;
        $predicate = Cart::query();
        foreach ($filters as $key => $filter) {
            if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
            {
                continue;
            }
    
            $predicate->where($key, $filter);
         }
        $carts = $predicate->with(['customer' => function ($q) {
            $q->select('user_id','name');
        }])->paginate($pageSize);

        foreach($carts as $cart){
            Log::info($cart);
            $cart['cart_items'] = CartItem::where([['cart_id','=',$cart->id],['status','=', 'PENDING']])->count();
        }


        
        foreach($carts as $key => $cart){
            $carts[$key]['total_amount'] = $cart->totalAmount(); 
            Log:info($cart->totalAmount());
      
            // $carts[$key]['cartItemInfo'] = CartItem::with(['part' => function ($q) {
            //     $q->select('id','name');
            // }])->get();
          //  $carts[$key]['part'] = CartItem::find($carts[$key]->cartItems->id);
        }

        return $carts;
    }

    public function viewUserCartItems($id){
        $carts = Cart::with('cartItems')->where("customer_id", $id)->get();

        
        foreach($carts as $key => $cart){
            $carts[$key]['total_amount'] = $cart->totalAmount();
            if(count($carts[$key]->cartItems) > 0){
                foreach($carts[$key]->cartItems as $cartItem){

                    $partImage = PartImage::where('part_id',$cartItem->part_id)->first();
                    Log::info($partImage);
                    if($partImage != null){
                        $cartItem['image_url'] = url("system/autopart/image/{$partImage->name}");
                    }
                    else{
                        $cartItem['image_url'] = null;
                    }       

                    $cartItem['part'] = AutoPart::find($cartItem->part_id);
                     unset($cartItem['part']->description);
                     unset($cartItem['part']->subcategory_id);
                     unset($cartItem['part']->model_id);
                     unset($cartItem['part']->unit_price);
                     unset($cartItem['part']->fit_note);
                     unset($cartItem['part']->condition);
                     unset($cartItem['part']->quantity);
                     unset($cartItem['part']->universal_part);
                     unset($cartItem['part']->created_at);
                     unset($cartItem['part']->updated_at);
                     unset($cartItem['part']->status);
                     unset($cartItem['part']->seller_id);
                }
            }
            unset($carts[$key]->partImage);
        }

        return $carts;
    }

    public function mGetCart($customer_id, $device_id)
    {
       $predicate = Cart::query();
       $predicate->with('cartItems');
       $cart = null;

        if($customer_id)
        {
            $cart = $predicate->where([["customer_id","=",$customer_id],["device_id","=",$device_id]])
                        ->orWhere("customer_id",$customer_id)
                        ->first();
        }else{
            $cart = $predicate->where('device_id','=',$device_id)->first();  
        }

        if(!$cart) $cart = $this->createCart(null,$device_id,$customer_id);
        
        if(count($cart->cartItems) > 0){
            foreach($cart->cartItems as $cartItem){

                $partImage = PartImage::where('part_id',$cartItem->part_id)->first();
                Log::info($partImage);
                if($partImage != null){
                    $cartItem['image_url'] = url("system/autopart/image/{$partImage->name}");
                }
                else{
                    $cartItem['image_url'] = null;
                }  
                
                $cartItem["auto_part_name"] = $cartItem->autoPart->name;
                unset($cartItem["autoPart"]);

             }
        }

        return $cart;
    }

    public function getImagesForCartItem($cartItems){
        Log::notice('part info');

    }



    public function deleteCartItem($id){

        $cartItem = CartItem::where('id',$id)->first();
        return $cartItem->delete();
    }

    public function removeCart($cart_id){

        $cart = Cart::where('id',$cart_id)->first();
          $this->deleteCartItemsForAParticularCart($cart_id);
        return $cart->delete();
    }

public function getCartById($id)
{
    return Cart::where("id", $id)->first();
}

public function getCustomerById($id)
{
    return Cart::where("customer_id", $id)->first();
}

public function getCartByDeviceId($device_id)
{
    return Cart::with('cartItems')->where("device_id",'=', $device_id)->first();
}


public function getCartItemById($id)
{
    return CartItem::where("id", $id)->first();
}

public function deleteCartItemsForAParticularCart($id)
{
    $cartItems = CartItem::where("cart_id", $id)->get();
    foreach($cartItems as $cartItem){
        $cartItem->delete();
    }
}



public function getPartById($part_id)
{
    return AutoPart::where("id", $part_id)->first();
}

public function getAddressById($id)
{
    return Address::where("id", $id)->first();
}

}