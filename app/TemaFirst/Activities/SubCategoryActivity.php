<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\SubCategoryRepo;
use App\TemaFirst\Repos\CategoryRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Repos\AutoPartsRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\AuthTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;



class SubCategoryActivity extends BaseActivity
{
	use AuthTrait;

    protected $subCategoryRepo;
    protected $categoryRepo;
	protected $apiResponse;
    protected $autoPartsRepo;

	public function __construct(
        SubCategoryRepo $subCategoryRepo,
        CategoryRepo $categoryRepo,
		ApiResponse $apiResponse,
        AutoPartsRepo $autoPartsRepo
	)
    {
        $this->subCategoryRepo = $subCategoryRepo;
        $this->categoryRepo = $categoryRepo;
		$this->apiResponse = $apiResponse;
        $this->autoPartsRepo = $autoPartsRepo;
    }

    public function addNewSubCategory($data)
    {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddSubCategoryParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }


      //check if category
      if(!$this->categoryRepo->getCategoryById($data['category_id'])){
        $message = "The specified category does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->generalError($message);
      }

      // Check if subcategory name exists if name is specified
  
          if($this->subCategoryRepo->subCatExists($data['name']))
          {
              $message = "The specified Subcategory {$data['name']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }
    

      $addSubCategory= $this->subCategoryRepo->createSubCategory($data);
      if($addSubCategory)
      {
          unset($addSubCategory['UID']);
          $message = "SubCategory : {$data['name']} added successfully";
          Log::notice($message);
        //  AuthEvents::CategoryHasAdded($addSubCategory);
          return $this->apiResponse->success($message, ["data" => $addSubCategory->toArray()] );
      }
      else
      {
          $message = "Unable to add subcategory{$data['name']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }


      //editing and updating categories
      public function updateSubCategory($data)
      {
  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiEditSubCategoryParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }
            if(isset($data['make_id'])){
            //check if category exist and update
            if(!$this->subCategoryRepo->getCategoryById($data['category_id'])){
                $message = "The specified category does not exists";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
            }

      }


      // Check if category name exists if name is specified
  

      $entity = $this->subCategoryRepo->getSubCategoryById($data['sub_category_id']);

                  // Check if email exists if email is specified
        if(isset($data['name']) && $data['name'] != $entity->name) 
        {
            if($this->subCategoryRepo->subCategoryExists($data['name'],$data['sub_category_id']))
            {
                $message = "The specified name {$data['name']} already exist";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
            }
        }
    


          $addedSubCategory= $this->subCategoryRepo->updateSubCategoryInfo($data,$entity->id);
          if($addedSubCategory)
          {
              unset($addedSubCategory['UID']);
              $message = "SubCategory : {$data['name']} updated successfully";
              Log::notice($message);
              return $this->apiResponse->success($message, ["data" => $addedSubCategory->toArray()] );
          }
          else
          {
              $message = "Unable to add Subcategory{$data['name']}";
              ErrorEvents::apiErrorOccurred($message);
              return $this->apiResponse->generalError($message);
          }
 }


            public function getSubCategories($subcategories)
            {
                // fetching subcategories
                $entity = $this->subCategoryRepo->getAllSubCategories($subcategories);
                if($entity)
                {
                    $message = "SubCategories results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $entity]);
                }
        
                $errMsg = "Could not get SubCategories";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function getCategorySubCategories($uid)
            {
                $category = $this->categoryRepo->getCategoryByUid($uid);
                //check if category exist
               if(!$category){
                 $message = "The specified category does not exist";
                 ErrorEvents::apiErrorOccurred($message, "warning");
                 return $this->apiResponse->generalError($message);
              }

                $subcategories = $this->subCategoryRepo->getAllCategorySubCategories($category->id);
                if($subcategories)
                {
                    $message = "SubCategories results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $subcategories]);
                }
        
                $errMsg = "Could not get SubCategories";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }

            public function mGetCategorySubcategories($filters, $uid){
                $category = $this->categoryRepo->getCategoryByUid($uid);
                if(!$category){
                    $message = "The specified category does not exist";
                    ErrorEvents::apiErrorOccurred($message, "warning");
                    return $this->apiResponse->generalError($message);
                }

                $subcategories = $this->subCategoryRepo->mGetSubcategoriesGroupByCategoryId($filters,$category->id);
                if($subcategories)
                {
                    $message = "SubCategories results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $subcategories]);
                }
        
                $errMsg = "Could not get SubCategories";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }

            public function getPartsGrouped($filters)
            {
                // fetching categories with subcategories
                $entity = $this->subCategoryRepo->getPartsGroup($filters);
                if($entity)
                {
                    $message = "Grouped parts results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $entity]);
                }
        
                $errMsg = "Could not get grouped data";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function removeSubCategory($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDeleteSubCategoryParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
               
                log::notice($data);
                $subcategories = $this->subCategoryRepo->getSubCategoryById($data['sub_category_id']);

                //check if subcategory exist
                if(!$this->subCategoryRepo->getSubCategoryById($data['sub_category_id'])){
                $message = "The specified subcategory does not exist";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->generalError($message);
                }
                
                $RemoveSubCategory= $this->subCategoryRepo->deleteSubCategory($data['sub_category_id']);
                if($RemoveSubCategory)
                {
                    $message = "SubCategory : {$data['sub_category_id']} deleted successfully";
                    Log::notice($message);
                   // AuthEvents::systemUserRoleHasDeleted($RemoveSubCategory);
                    return $this->apiResponse->success($message, ["data" => $RemoveSubCategory->toArray()] );
                }
                else
                {
                    $message = "Unable to delete for {$data['sub_category_id']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }

            public function addImage(Array $data)
            {
                // Validate request parameters
                $missingParams = Validator::validateRequiredParams($this->apiAddSubCategoryImageParams, $data);
                if(!empty($missingParams))
                {
                    $errors = Validator::convertToRequiredValidationErrors($missingParams);
                    ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

                    return $this->apiResponse->validationError(
                        ["errors" => $errors]
                    );
                }

                // Upload Subcategory Image
                $subCategoryImage = $this->subCategoryRepo->uploadImage($data);
                if($subCategoryImage)
                {
                    $message = "Image Uploaded Successfully";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $subCategoryImage]);
                }

                $errMsg = "Could not upload image";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function subCategoryAutoPartDetails($filters, $uid){

                $subcategory = $this->subCategoryRepo->getSubCategoryByUid($uid);

                if(!$subcategory){
                    $message = "The specified sub-category does not exists";
                    ErrorEvents::apiErrorOccurred($message, "warning");
                    return $this->apiResponse->notFoundError($message);
                }

                $auto_parts = $this->autoPartsRepo->getSubCateoryTopGrossingAutoParts($filters, $subcategory->id);
                
                if(isset($auto_parts) && count($auto_parts) > 0)
                {
                foreach($auto_parts as $key => $auto_part)
                {
                    $auto_part["revenue_generated"] = $auto_part->amountSold();
                    $auto_part["items_sold"] = $auto_part->quantitySold();
                    
                    unset($auto_parts[$key]["order_items"]);
                    unset($auto_part->orderItems);
                }
            }
        
            $message = "Top grossing items results";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $auto_parts]);
        }
}