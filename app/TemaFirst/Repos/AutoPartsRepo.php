<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\Permission;
use App\Models\SystemUser;
use App\TemaFirst\Utilities\Generators;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\TemaFirst\Utilities\Constants;
use App\Mail\WelcomeMail;
use App\Notifications\WelcomeUser;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use App\Models\CarModel;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\CarYear;
use App\Models\CarMake;
use App\Models\Order;
use App\Models\Seller;
use App\Models\AutoPart;
use App\Models\Specification;
use App\Models\PartImage;
use App\Activity;
use App\Exports\SystemUsersExport;
use App\Exports\ExportAuditTrail;
use App\Exports\RolesExport;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class AutoPartsRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(Autopart $auto_part)
    {
        $this->model = $auto_part;
    }

    public function createAutoPart(Array $data)
    {
    	$auto_part = new AutoPart;

        $auto_part->name = $data['name'];
        $auto_part->subcategory_id = $data['subcategory_id'];
        $auto_part->model_id = $data['model_id'];
        $auto_part->unit_price = $data['unit_price'];
        $auto_part->condition = $data['condition'];
        $auto_part->quantity = $data['quantity'];
        $auto_part->unit_price = $data['unit_price'];
        $auto_part->seller_id = $data['seller_id'];
        $auto_part->description = $data['description'];
        $auto_part->universal_part = $data['universal_part'] ?? false;
        $auto_part->fit_note = $data['fit_note'] ?? null;
        $auto_part->status = Constants::STATUS_ENABLED;
        $auto_part->created_at = Carbon::now();
        $auto_part->updated_at = Carbon::now();

        // $auto_part->UID =  Generators::generateUniq();


    	if($auto_part->save())
    	{
    		return $auto_part;
    	}
    	return null;
    }

    public function changePartStatus($id)
    {
        $auto_part = AutoPart::where("id", $id)->first();

        if(!isset($auto_part)){
            return back()->withErrors(['error' => 'Auto Part cannot be found']);
        }

        if($auto_part->status == Constants::STATUS_ENABLED){
            $auto_part->status = Constants::STATUS_DISABLED;
        }else{
            $auto_part->status = Constants::STATUS_ENABLED;
        }


    	if($auto_part->save())
    	{
    		return $auto_part;
    	}
    	return null;
    }


    public function uploadImage(Array $data)
    {
    	$image = new PartImage;

        $image->name =  Generators::generateUniq();
        $image->part_id = $data['auto_part_id'];
        $image->base64 = preg_replace('/data:[\s\S]+?base64,/', '', str_replace('"','',$data['base64']));
        
        if($image->save())
        {
            return $image;
        }

        return null;
      
    }

    public function updateAutoPartImage(Array $data, $id)
    {
        $auto_part_image = PartImage::where("id", $id)->first();

        if(isset($auto_part_image)){
        $auto_part_image->base64 = preg_replace('/data:[\s\S]+?base64,/', '', str_replace('"','',$data['base64']));
            
            if($auto_part_image->update())
            {
                return $auto_part_image;
            }

        }

        return null;
      
    }


    public function createSpecifications(Array $data)
    {
    	$specs = new Specification;

        $specs->key = $data['key'];
        $specs->value = $data['value'];
        $specs->auto_part_id = $data['auto_part_id'];

    	if($specs->save())
    	{
    		return $specs;
    	}
    	return null;
    }

    public function updateAutoPart($data, $id)
    {
        $auto_part = AutoPart::where("id", $id)->first();

        $auto_part->name = $data['name'];
        $auto_part->subcategory_id = $data['subcategory_id'];
        $auto_part->model_id = $data['model_id'];
        $auto_part->unit_price = $data['unit_price'];
        $auto_part->condition = $data['condition'];
        $auto_part->quantity = $data['quantity'];
        $auto_part->unit_price = $data['unit_price'];
        $auto_part->seller_id = $data['seller_id'];
        $auto_part->description = $data['description'];
        $auto_part->universal_part = $data['universal_part'] ?? false;
        $auto_part->fit_note = $data['fit_note'] ?? null;
        $auto_part->updated_at = Carbon::now();


    	if($auto_part->update())
    	{
    		return $auto_part;
    	}
    	return null;
    }


    public function partRestock($data, $id)
    {
        $auto_part = AutoPart::where("id", $id)->first();

        $auto_part->quantity = $data['quantity'];
        $auto_part->updated_at = Carbon::now();


    	if($auto_part->update())
    	{
    		return $auto_part;
    	}
    	return null;
    }

    public function searchAutoParts(Array $data){
        $name=$data['name']??null;
        $sub_category_name=$data['sub_category_name']??null;
        $category_name=$data['category_name']??null;
        $model_name=$data['model_name']??null;
        $unit_price=$data['unit_price']??null;
        $condition=$data['condition']??null;
        $quantity=$data['quantity']??null;
        $seller_name=$data['seller_name']??null;
        $description=$data['description']??null;
        $seller_name=$data['seller_name']??null;
        $seller_name=$data['created_at']??null;
        $seller_name=$data['updated_at']??null;
        $search=$data['search']??null;

        $query= AutoPart::query();

        if(isset($name)){
            $query->where('name', 'ilike', '%'.$name.'%');
        }
        if(isset($unit_price)){
            $query->where('unit_price', 'ilike', '%'.$unit_price.'%');
        }
        if(isset($condition)){
            $query->where('condition', 'ilike', '%'.$condition.'%');
        }

        if(isset($quantity)){
            $query->where('quantity', 'ilike', '%'.$quantity.'%');
        }

        if(isset($description)){
            $query->where('description', 'ilike', '%'.$description.'%');
        }

        if(isset($created_at)){
            $query->where('created_at', 'ilike', '%'.$created_at.'%');
        }
        if(isset($updated_at)){
            $query->where('updated_at', 'ilike', '%'.$updated_at.'%');
        }

        if(isset($search)){
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere('unit_price', 'ilike', '%'.$search.'%')       
                    ->orWhere('condition', 'ilike', '%'.$search.'%')
                    ->orWhere('quantity', 'ilike', '%'.$search.'%')
                    ->orWhere('description', 'ilike', '%'.$search.'%');
        }

        if(isset($sub_category_name)){
            $query->whereHas('subCategories', function ($query) use ($sub_category_name){
                $query->where('name', 'ilike', '%' . $sub_category_name .'%');
            });
        }
        if(isset($category_name)){
            //First get category
            $category = Category::where('name', 'ilike', '%' . $category_name . '%')->first();

            if(isset($category)){
                $query->whereHas('subCategories', function ($q) use ($category){
                    $q->where('category_id', '=',$category->id);
                });
            }
        }

        $auto_parts = $query->orderBy('name', 'asc')->with('partImage')->get();

        foreach ($auto_parts as $key => $auto_part) {
            $auto_parts[$key]['image_url'] = $auto_parts[$key]->partImage ? url("/image/website/{$auto_parts[$key]->id}") : null;
            $auto_parts[$key]['category'] = Category::find($auto_parts[$key]->subCategory->category_id);
            unset($auto_parts[$key]->partImage);
         }
    


            return $auto_parts;
    }


 public function autoPartExists($name)
 {
    $auto_part = AutoPart::where('name',$name)->first();
    if($auto_part)
        return true;
    else
        return false;
}

public function getCarModelById($id)
{
    return CarModel::where("id", $id)->first();
}

public function getSubCategoryById($id)
{
    return SubCategory::where("id", $id)->first();
}

public function getAutoPartByUid($uid)
{
    return AutoPart::where("UID", $uid)->first();
}

public function getPartImageByName($name){
    return PartImage::where("name", $name)->first();
}

public function getAutoPartByid($id)
{
    return AutoPart::where("id", $id)->first();
}

public function getSellerById($id)
{
    return Seller::where("id", $id)->first();
}


public function getAnAutoPartById($id)
{
    $auto_parts = AutoPart::where("id", $id)->with('subCategory')->with('carModelInfo')->first();
    
    $partImages = [];
    foreach($auto_parts->images as $image){
        array_push($partImages,url("system/autopart/image/{$image->name}"));
    }

    $auto_parts['image_urls'] = $partImages;
    $auto_parts['category'] = Category::find($auto_parts->subCategory->category_id);
    $auto_parts['car_make'] = CarMake::find($auto_parts->carModelInfo->make_id);
    $auto_parts['year'] = CarYear::find($auto_parts->carModelInfo->year_id);
    $auto_parts['specs'] = Specification::where("auto_part_id", $id)->get();

     return $auto_parts;

}

public function getAllAutoSubCategories($filters, $id)
{
    $pageSize = $filters['pageSize'] ?? 17;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
     }

     $auto_parts = $predicate->with('subCategory')->orderBy('name','ASC')->paginate($pageSize);
     foreach ($auto_parts as $key => $auto_part) {

         $partImages = [];
         foreach($auto_part->images as $image){
             array_push($partImages,url("system/autopart/image/{$image->name}"));
         }

         $auto_parts[$key]['image_urls'] = $partImages;
         $auto_parts[$key]['category'] = Category::find($auto_parts[$key]->subCategory->category_id);
         unset($auto_part->images);
      }

     return $auto_parts;

}



public function viewGrossingPartItems($filters){
    $pageSize = $filters['pageSize'] ?? 5;
    $query = Order::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $query->where($key, $filter);
     }
    
    $query->where('status','Checked_out')->with('itemsInOrder');
    
    if(isset($filters['filter_date']) && $filters['filter_date'] == 'month'){
        $query->whereMonth('created_at',Carbon::now()->month);
    }else if(isset($filters['filter_date']) && $filters['filter_date'] == 'week'){
        $query->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek());
    }else{
        $query->whereDate('created_at', Carbon::today());
    }

    $orders = $query->paginate($pageSize);

    //Log::info($orders);
    $order_items = [];

    foreach($orders as $order){
        unset($order->shipping_id);
        unset($order->reference_number);
        unset($order->UID);
        unset($order->created_at);
        unset($order->updated_at);
        unset($order->customer_id);
        unset($order->status);
       array_push($order_items,...$order['itemsInOrder']);
    }

    foreach($order_items as $order_item){
        $order_item['items_sold'] = $order_item->quantity;
        unset($order_item->quantity);
        unset($order_item->created_at);
        unset($order_item->order_id);
        $part = AutoPart::where('id',$order_item->part_id)->first();
        $order_item['quantity_available'] = $part->quantity;
    }
    $removedDuplicateParts = $this->removeDuplicateParts($order_items,[]);
      
    foreach($removedDuplicateParts as $removedDuplicatePart){
        $removedDuplicatePart['revenue'] = $removedDuplicatePart->items_sold * $removedDuplicatePart->unit_price;
    }
   
    return count($removedDuplicateParts) > 0 ? $removedDuplicateParts : [] ;

    }


        public function removeDuplicateParts($oldArray, $newArray){
            if(count($oldArray) > 0){
                $current_item = array_shift($oldArray);
                if(count($newArray) == 0){
                    $newArray[] = $current_item;
                    return  $this->removeDuplicateParts($oldArray, $newArray);
                } else{
                    foreach($newArray as $item){
                        if($current_item->part_name == $item->part_name){
                            $item->items_sold += $current_item->items_sold;
                            return  $this->removeDuplicateParts($oldArray, $newArray);

                        }elseif($current_item->part_name != $item->part_name){
                            $newArray[] = $current_item;
                            return  $this->removeDuplicateParts($oldArray, $newArray);
                        }
                    }
                }
            }else 
            return $newArray;
        }

public function revenueStats($filters){

    $query->where('status','Checked_out')->with('itemsInOrder');
    
    if(isset($filters['filter_date']) && $filters['filter_date'] == 'week'){
        $query->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->with();
    }else{
        $query->whereDate('created_at', Carbon::today());
    }

    $orders = $query->get();

}

public function getSpecsById($id)
{
    return Specification::where("id", $id)->first();
}

public function getImageById($id)
{
    return PartImage::where("name", $id)->first();
}

public function deleteImage($id)
{
    $image = PartImage::where("name", $id)->first();
    $image->delete();

    return $image;
}

public function deleteAutoParts($id)
{
    $part = AutoPart::where("id" ,'=', $id)->first();
    $part->delete();

    return $part;
}

public function deleteSpecs($id)
{
    $specs = Specification::where("id", $id)->first();
    $specs->delete();

    return $specs;
}


public function getAllAutoParts($filters){
    $pageSize = $filters['pageSize'] ?? 9;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $auto_parts = $predicate->with('subCategory')->orderBy('name','ASC')->paginate($pageSize);
    foreach ($auto_parts as $key => $auto_part) {

        $partImages = [];
        foreach($auto_part->images as $image){
            array_push($partImages,url("system/autopart/image/{$image->name}"));
        }
    
        $auto_parts[$key]['image_urls'] = $partImages;
        $auto_parts[$key]['category'] = Category::find($auto_parts[$key]->subCategory->category_id);
        unset($auto_part->images);
     }

    return $auto_parts;
}

public function getAllWebAutoParts(){
    return AutoPart::paginate(20);
}

public function mGetPopularAutoParts($filters)
{

    $pageSize = $filters['pageSize'] ?? 20;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $auto_parts = $predicate->with('specifications')->paginate($pageSize);
    foreach($auto_parts as $key => $auto_part)
    {
        $partImages = [];
        foreach($auto_part->images as $image)
        {
            array_push($partImages,url("system/autopart/image/{$image->name}"));
        }

        unset($auto_parts[$key]->images);
    
        $auto_parts[$key]['image_urls'] = $partImages;
    }

    return $auto_parts;
}

public function mGetNewestArrivalAutoParts($filters)
{
    $pageSize = $filters['pageSize'] ?? 20;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $auto_parts = $predicate->with('specifications')->paginate($pageSize);

    foreach($auto_parts as $key => $auto_part)
    {
        $partImages = [];
        foreach($auto_part->images as $image)
        {
            array_push($partImages,url("system/autopart/image/{$image->name}"));
        }
        unset($auto_parts[$key]->images);
    
        $auto_parts[$key]['image_urls'] = $partImages;
    }

    return $auto_parts;
}

public function mGetMoreAutoParts($filters)
{
    $pageSize = $filters['pageSize'] ?? 20;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $auto_parts = $predicate->with('specifications')->paginate($pageSize);

    foreach($auto_parts as $key => $auto_part)
    {
        $partImages = [];
        foreach($auto_part->images as $image)
        {
            array_push($partImages,url("system/autopart/image/{$image->name}"));
        }

        unset($auto_parts[$key]->images);
    
        $auto_parts[$key]['image_urls'] = $partImages;
    }

    return $auto_parts;

}

public function getNewUploadedAutoPartsGroup($filters)
{
    $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_DAY;

    $predicate = AutoPart::query();

    if($filter_date == Constants::FILTER_DATE_MONTH){
       return $predicate->get()
        ->groupBy(function($val){
            return Carbon::parse($val->created_at)->format('W');
        });
    }else if($filter_date== Constants::FILTER_DATE_WEEK){
        $predicate->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())
        ->get()
        ->groupBy(function ($val){
            return Carbon::parse($val->created_at)->format('d');
        });
    }else{
        return $predicate->whereDate('created_at', Carbon::today())->get();
    }

}

public function getTopGrossingAutoParts($filters)
{
    $pageSize = $filters['pageSize'] ?? 5;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_DAY;

    if($filter_date == Constants::FILTER_DATE_MONTH){
        $predicate->whereHas('orderItems', function($query){
            $query->whereMonth('created_at', Carbon::now()->month);
        });
    }else if($filter_date== Constants::FILTER_DATE_WEEK){
        $predicate->whereHas('orderItems', function($query){
            $query->where('created_at', '>', Carbon::now()->startOfWeek())
            ->where('created_at', '<', Carbon::now()->endOfWeek());
        });
    }else{
        $predicate->whereHas('orderItems', function($query){ 
            //$query->whereDate('created_at', Carbon::today());
        });
    }

    return $predicate->paginate($pageSize,['id','name','quantity']);
}

public function getSubCateoryTopGrossingAutoParts($filters, $sub_category_id)
{
    $pageSize = $filters['pageSize'] ?? 18;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }
    $predicate->where('subcategory_id',$sub_category_id);

    return $predicate->paginate($pageSize,['id','name','quantity']);
}

public function mGetRelatedAutopartList($filters, AutoPart $auto_part)
{
    $pageSize = $filters['pageSize'] ?? 20;
    $predicate = AutoPart::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $auto_parts = $predicate->with('specifications')->where('subcategory_id',$auto_part->subcategory_id)->whereNotIn('id',[$auto_part->id])->paginate($pageSize);

    foreach($auto_parts as $key => $auto_part)
    {
        $partImages = [];
        foreach($auto_part->images as $image)
        {
            array_push($partImages,url("system/autopart/image/{$image->name}"));
        }

        unset($auto_parts[$key]->images);
    
        $auto_parts[$key]['image_urls'] = $partImages;
    }

    return $auto_parts;
}

}