<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\CategoryRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\AuthTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Events\AuditEvent;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Repos\SystemUserRepo;



class CategoryActivity extends BaseActivity
{
	use AuthTrait;

    protected $categoryRepo;
    protected $verificationRepo;
	protected $apiResponse;
    protected $systemUserRepo;

	public function __construct(
        CategoryRepo $categoryRepo,
        VerificationRepo $verificationRepo,
		ApiResponse $apiResponse,
        SystemUserRepo $systemUserRepo
	)
    {
        $this->categoryRepo = $categoryRepo;
        $this->verificationRepo = $verificationRepo;
		$this->apiResponse = $apiResponse;
        $this->systemUserRepo = $systemUserRepo;
    }

    public function mGetCategories($filters){
        $categories = $this->categoryRepo->mGetCategories($filters);
        $message = "Categories retrieved successfully";
        Log::notice($message);
        return $this->apiResponse->success($message,["data" => $categories]);
    }

    public function addNewCategory($request)
    {
    
    $data = $request->post();

  	// Validate request parameters
      $missingParams = Validator::validateRequiredParams($this->apiAddCategoryParams, $data);
      if(!empty($missingParams))
      {
          $errors = Validator::convertToRequiredValidationErrors($missingParams);
          ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

          return $this->apiResponse->validationError(
              ["errors" => $errors]
          );
      }

      // Check if category name exists if email is specified
      if(isset($data['name']))
      {
          if($this->categoryRepo->categoryExists($data['name']))
          {
              $message = "The specified name {$data['name']} already exists";
              ErrorEvents::apiErrorOccurred($message, "warning");
              return $this->apiResponse->generalError($message);
          }
      }

      $addCategory= $this->categoryRepo->createCategory($data);
      if($addCategory)
      {
          unset($addCategory['UID']);
          $message = "Category : {$data['name']} registered successfully";
          Log::notice($message);
          AuthEvents::CategoryHasAdded($addCategory);
          
          AuditEvent::logEvent($request,$addCategory, "New category with name {$addCategory->name} was created");
        
          return $this->apiResponse->success($message, ["data" => $addCategory->toArray()] );
      }
      else
      {
          $message = "Unable to add category{$data['name']}";
          ErrorEvents::apiErrorOccurred($message);
          return $this->apiResponse->generalError($message);
      }
    }

    public function getSubCategoriesData($subcategories)
    {
        // fetching subcategories
        $entity = $this->categoryRepo->getAllCategorySubCategoriesData($subcategories);
        if($entity)
        {
            $message = "Categories with SubCategories results";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $entity]);
        }

        $errMsg = "Could not get Categories with SubCategories";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }

    public function listCategoryItems()
    {
        // fetching category items
        $entity = $this->categoryRepo->listCategoryItems();
        Log::info($entity);
        if(count($entity )>= 0)
        {
            $message = "Categories with items sold and revenue results";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $entity]);
        }

        $errMsg = "Could not get results";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }


      //editing and updating categories
      public function updateCategory($request)
      {
          $data = $request->post();
        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiEditCategoryParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
  
            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
  
           $category = $this->categoryRepo->getCategoryById($data['category_id']);
  
              // Check if name exists if name is specified
              if(isset($data['name']) && $data['name'] != $category->name) 
              {
                  if($this->categoryRepo->categoryExists($data['name']))
                  {
                      $message = "The specified Category name {$data['name']} already exists";
                      ErrorEvents::apiErrorOccurred($message, "warning");
                      return $this->apiResponse->generalError($message);
                  }
              }

              
              $addedCategory= $this->categoryRepo->updateCategoryInfo($data, $category->id);
              if($addedCategory)
              {
                  unset($addedCategory['UID']);
                  $message = "Category : updated successfully to {$data['name']}";
                  
                  AuditEvent::logEvent($request,$addedCategory,$message);

                  Log::notice($message);
                  AuthEvents::CategoryHasAdded($addedCategory);
                  return $this->apiResponse->success($message, ["data" => $addedCategory->toArray()] );
              }
              else
              {
                  $message = "Unable to complete update for category {$data['name']}";
                  ErrorEvents::apiErrorOccurred($message);
                  return $this->apiResponse->generalError($message);
              }
            }

            public function getCategories($categories)
            {
                // fetching Categories
                $entity = $this->categoryRepo->getAllCategories($categories);
                if($entity)
                {
                    $message = "Categories results";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $entity]);
                }
        
                $errMsg = "Could not get Categories";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }


            public function removeCategory($request)
            {
                $data =  $request->post();    
                // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDeleteCategoryParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $category = $this->categoryRepo->getCategoryById($data['category_id']);
                //Check if category exist
                if(!$category){
                    $message = "category not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }

                // //Check if category is having children
                // if($category->subCategories != null && $category->subCategories->count() > 0){
                //     $message = "Unable to delete for {$category['name']}";
                //     ErrorEvents::apiErrorOccurred($message);
                //     return $this->apiResponse->generalError($message);
                // }
                
                $removeCategory= $this->categoryRepo->deleteCategory($data['category_id']);
                if($removeCategory)
                {
                    $message = "Category : {$category['name']} deleted successfully";
                    
                    AuditEvent::logEvent($request,$category,$message);

                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => null] );
                }
                else
                {
                    $message = "Unable to delete for {$category['name']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }


            public function removeCategoryImage($data)
            {
                      // Validate request parameters
              $missingParams = Validator::validateRequiredParams($this->apiDeleteCategoryImageParams, $data);
              if(!empty($missingParams))
              {
                  $errors = Validator::convertToRequiredValidationErrors($missingParams);
                  ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
        
                  return $this->apiResponse->validationError(
                      ["errors" => $errors]
                  );
              }
                log::notice($data);
                $categoryImage = $this->categoryRepo->getCategoryImageById($data['category_id']);
                //Check if category exist
                if(!$categoryImage){
                    $message = "category Image not found";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->notFoundError($message);

                }
                
                $removeCategoryImage= $this->categoryRepo->deleteCategoryImage($data['category_id']);
                if($removeCategoryImage)
                {
                    $message = "Category Image : {$categoryImage['name']} deleted successfully";
                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => null] );
                }
                else
                {
                    $message = "Unable to delete for {$categoryImage['name']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
          
            }



            public function addImage(Array $data)
            {
                // Validate request parameters
                $missingParams = Validator::validateRequiredParams($this->apiAddCategoryImageParams, $data);
                if(!empty($missingParams))
                {
                    $errors = Validator::convertToRequiredValidationErrors($missingParams);
                    ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

                    return $this->apiResponse->validationError(
                        ["errors" => $errors]
                    );
                }

                // Upload Category Image
                $categoryImage = $this->categoryRepo->uploadImage($data);
                if($categoryImage)
                {
                    $message = "Image Uploaded Successfully";
                    Log::notice($message);
                    $response_data['image_url'] = url("/category_image/website/{$categoryImage->name}");
                    return $this->apiResponse->success($message, ["data" => $response_data]);
                }

                $errMsg = "Could not upload image";
                ErrorEvents::apiErrorOccurred($errMsg);
                return $this->apiResponse->generalError($errMsg);
            }

            public function searchCategory($data)
            {
              // Attempt to search category
              $category= $this->categoryRepo->searchCategory($data);
              if($category)
              {
                  $message = "category search results";
                  Log::notice($message);
                  return $this->apiResponse->success($message, ["data" => $category] );
              }
              else
              {
                  $message = "Unable to fetch data";
                  ErrorEvents::apiErrorOccurred($message);
                  return $this->apiResponse->generalError($message);
              }
            }


}