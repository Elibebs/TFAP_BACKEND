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
use App\Models\SubCategory;
use App\Models\AutoPart;
use App\Models\SubCategoryImage;
use App\Models\Category;
use App\Activity;
use App\Exports\SystemUsersExport;
use App\Exports\ExportAuditTrail;
use App\Exports\RolesExport;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class SubCategoryRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(SubCategory $subcategory)
    {
        $this->model = $subcategory;
    }

    public function createSubCategory(Array $data)
    {
    	$subcategory = new SubCategory;

        $subcategory->name = $data['name'];
        $subcategory->category_id = $data['category_id'];
        $subcategory->UID =  Generators::generateUniq();


    	if($subcategory->save())
    	{
    		return $subcategory;
    	}
    	return null;
    }

    public function updateSubCategoryInfo($data, $id)
    {
        $subcategory = Subcategory::where("id", $id)->first();

        $subcategory->name = $data['name'];
        if(isset($data['category_id'])){
            $subcategory->category_id = $data['category_id']; 
        }
        else{
            $subcategory->category_id = null;
        }


        if($subcategory->update())
            return $subcategory;
        else
            return null;
    }

    public function getPartsGroup($filters)
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

        $categories = $predicate->with('categoryImage')->orderBy('name','ASC')->paginate($pageSize);
        foreach ($categories as $key => $category) {
            $categories[$key]['sub_category_count'] = $category->subCategoriesCount();
            $categories[$key]['sub_categories'] = $category->subCategoryItems;

            
            foreach($category->sub_categories as $subCategory){
                $subCategory['parts'] = $subCategory->autoParts;
                unset($subCategory->autoParts);
            
            }


            $categories[$key]['image_url'] = $categories[$key]->categoryImage ? url("/category_image/website/{$categories[$key]->id}") : null;
            unset($categories[$key]->categoryImage);
            unset($categories[$key]->subCategoryItems);

         }

        return $categories;
    }


 public function subCategoryExists($name,$sub_category_id)
 {
    $subcategory = Subcategory::where([['name',$name],['id','!=',$sub_category_id]])->first();
    if($subcategory)
        return true;
    else
        return false;
}

public function getSubCategoryById($id)
{
    return Subcategory::where("id", $id)->first();
}


public function subCatExists($name)
{
    return Subcategory::where("name", $name)->first();
}

public function getCategoryById($sub_category_id)
{
    return Category::where("id", $sub_category_id)->first();
}

public function deleteSubCategory($id)
{
    $subcategory = Subcategory::where("id", $id)->first();
    $subcategory->delete();

    return $subcategory;
}

public function getAllSubCategories($subcategory){
  
    $subcategory=Subcategory::all();

    return $subcategory;
    
}

public function getAllCategorySubCategories($id){
    return Subcategory::where("category_id", $id)->get();
}

public function mGetSubcategoriesGroupByCategoryId($filters, $id)
{
    $pageSize = $filters['pageSize'] ?? 20;
    $predicate = Subcategory::query();
    foreach ($filters as $key => $filter)
    {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    $predicate->where("category_id", $id)->whereHas('autoParts');

    $subCategories = $predicate->with('autoParts')->orderBy('name','ASC')->paginate($pageSize);
    foreach($subCategories as $key => $subCategory)
    {
        
        foreach($subCategory->autoParts as $k => $autoPart)
        {   $partImages = [];
            foreach($autoPart->images as $image){
                array_push($partImages,url("mobile/autopart/image/{$image->name}"));
            }
            //$subCategory->autoParts[$k]['image_url'] = $autoPart->partImage ? url("/image/website/{$autoPart->id}") : null;
            $subCategory->autoParts[$k]['image_urls'] = $partImages;
            $autoPart['specifications'] = $autoPart->specifications;
            unset($autoPart->images);
        }
    }

    return $subCategories;

}

public function getSubCategoryByUid($uid)
{
    return Subcategory::where("UID", $uid)->first();
}


public function uploadImage(Array $data)
{
    $image = new SubCategoryImage;

    $image->name =  Generators::generateUniq();
    $image->subcategory_id = $data['subcategory_id'];
    $image->base64 = $data['base64'];


    if($image->save())
    {
        return $image;
    }
    return null;
}

}