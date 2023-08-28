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
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Order;
use App\Models\AutoPart;
use App\Models\CategoryImage;
use App\Activity;
use App\Exports\SystemUsersExport;
use App\Exports\ExportAuditTrail;
use App\Exports\RolesExport;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class CategoryRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(Category $category, Subcategory $subcategory)
    {
        $this->model = $category;
        $this->model = $subcategory;

    }


    public function createCategory(Array $data)
    {
    	$category = new Category;

    	$category->name = $data['name'];
        $category->UID =  Generators::generateUniq();
    	// $category->created_at = Carbon::now();
        // $category->updated_at = Carbon::now();

    	if($category->save())
    	{
    		return $category;
    	}
    	return null;
    }

    public function updateCategoryInfo($data, $id)
    {
        $category = Category::where("id", $id)->first();

        $category->name = $data['name'];


        if($category->update())
            return $category;
        else
            return null;
    }


 public function categoryExists($name)
 {
    $category = Category::where('name',$name)->first();
    if($category)
        return true;
    else
        return false;
}

public function getAllCategorySubCategoriesData($filters)
{
    $pageSize = $filters['pageSize'] ?? 9;
    $predicate = Category::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $categories = $predicate->with('subCategories')->orderBy('name','ASC')->paginate($pageSize);
    foreach ($categories as  $category) {

        $partImage = CategoryImage::where('category_id',$category->id)->first();

        if($partImage != null){
            $category['image_url'] = url("category_image/website/{$partImage->name}");
        }
        else{
            $category['image_url'] = null;
        }
        //unset($categories[$key]->categoryImage);
        // unset($categories[$key]->subCategoryItems);

     }

    return $categories;
}

public function getCategoryById($id)
{
    return Category::where("id", $id)->first();
}

public function getCategoryImageById($category_id)
{
    return CategoryImage::where("category_id", $category_id)->first();
}

public function deleteCategoryImage($category_id){

    $category = CategoryImage::where('category_id',$category_id)->first();
    return $category->delete();
}

public function getCategoryByUid($uid)
{
    return Category::where("UID", $uid)->first();
}

public function deleteCategory($id){

    $category = Category::where('id',$id)->first();
    $subCategories = SubCategory::where('category_id',$id)->get();
    if(count($subCategories) > 0){
        foreach($subCategories as $subCategory){
            $subCategory->category_id = null;

            $subCategory->update();
        }
        return $category->delete();
    }
    else
    return $category->delete();
}

public function getAllCategories($categories){
  
    $categories=Category::all();

    return $categories;
    
}

public function mGetCategories($filters){
    $pageSize = $filters['pageSize'] ?? 9;
    $predicate = Category::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $categories = $predicate->with('subCategories')->with('categoryImage')->orderBy('name','ASC')->paginate($pageSize);
    foreach ($categories as $key => $category) {
        $categories[$key]['image_url'] = $categories[$key]->categoryImage ? url("/category_image/website/{$categories[$key]->id}") : null;
        unset($categories[$key]->categoryImage);
     }

    return $categories;
}


public function uploadImage(Array $data)
{
    $image = new CategoryImage;

    $image->name =  Generators::generateUniq();
    $image->category_id = $data['category_id'];
   // $image->base64 = $data['base64'];
    $image->base64 = preg_replace('/data:[\s\S]+?base64,/', '', str_replace('"','',$data['base64']));


    if($image->save())
    {
        return $image;
    }
    return null;
}

public function listCategoryItems(){
    $category_items = Order::where('status','Checked_out')->with('itemsInOrder')->get();
    $order_items = [];

    foreach($category_items as $category_item){
        //unset($category_item->id);
        unset($category_item->shipping_id);
        unset($category_item->reference_number);
        unset($category_item->UID);
        unset($category_item->created_at);
        unset($category_item->updated_at);
        unset($category_item->customer_id);
        unset($category_item->status);
       array_push($order_items,...$category_item['itemsInOrder']);
    }

    foreach($order_items as $order_item){
        $order_item['items_sold'] = $order_item->quantity;
        unset($order_item->quantity);
        unset($order_item->id);
        unset($order_item->created_at);
        unset($order_item->part_name);
        unset($order_item->order_id);
        $parts = AutoPart::where('id',$order_item->part_id)->first();
        $subcats = SubCategory::where('id',$parts->subcategory_id)->first();
        $category = Category::where('id',$subcats->category_id)->first();
        $order_item['category'] = $category->name;
    }

    $category_items_list = [];
    $category_items_list_without_duplicates = $this->removeDuplicateCategories($order_items,$category_items_list);

    foreach($category_items_list_without_duplicates as $category_items_list_without_duplicate){
        $category_items_list_without_duplicate['revenue'] = $category_items_list_without_duplicate->items_sold * $category_items_list_without_duplicate->unit_price;
    }
   
    return count($category_items_list_without_duplicates) > 0 ? $category_items_list_without_duplicates : [] ;
    

}


public function removeDuplicateCategories($oldArray, $newArray){
    if(count($oldArray) > 0){
        $current_item = array_shift($oldArray);
        if(count($newArray) == 0){
            $newArray[] = $current_item;
            return  $this->removeDuplicateCategories($oldArray, $newArray);
        } else{
            foreach($newArray as $item){
                if($current_item->category == $item->category){
                    $item->items_sold += $current_item->items_sold;
                    return  $this->removeDuplicateCategories($oldArray, $newArray);

                }elseif($current_item->category != $item->category){
                    $newArray[] = $current_item;
                    return  $this->removeDuplicateCategories($oldArray, $newArray);
                }
            }
        }
    }else 
    return $newArray;
}



public function searchCategory(Array $data){
    // $name=$data['name']??null;
    // $uid=$data['UID']??null;
    $search=$data['search']??null;

    $query= Category::query();

    if(isset($name)){
        $query->where('name', 'ilike', '%'.$name.'%');
    }

    if(isset($search)){
               $query->where('name', 'ilike', '%'.$search.'%');
    }


    // $categories = $query->orderBy('name', 'asc')->get();
    $categories = $query->with('subCategories')->orderBy('name','ASC')->get();
    foreach ($categories as  $category) {

        $partImage = CategoryImage::where('category_id',$category->id)->first();

        if($partImage != null){
            $category['image_url'] = url("category_image/website/{$partImage->name}");
        }
        else{
            $category['image_url'] = null;
        }
        //unset($category->UID);
        // unset($categories[$key]->subCategoryItems);

     }



        return $categories;

}

}