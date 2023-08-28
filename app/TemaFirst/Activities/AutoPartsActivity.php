<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Repos\CarModelRepo;
use App\TemaFirst\Repos\SubCategoryRepo;
use App\TemaFirst\Repos\AutoPartsRepo;
use App\TemaFirst\Repos\VerificationRepo;
use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Traits\AutoPartsTrait;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Events\AuthEvents;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\TemaFirst\Utilities\Generators;
use App\Notifications\ResetPassword;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Events\AuditEvent;



class AutoPartsActivity extends BaseActivity
{
	use AutoPartsTrait;

    protected $carModelRepo;
    protected $subCategoryRepo;
    protected $autoPartsRepo;
	protected $apiResponse;

	public function __construct(
        AutoPartsRepo $autoPartsRepo,
        CarModelRepo $carModelRepo,
        SubCategoryRepo $subCategoryRepo,
		ApiResponse $apiResponse
	)
    {
        $this->carModelRepo = $carModelRepo;
        $this->subCategoryRepo = $subCategoryRepo;
        $this->autoPartsRepo = $autoPartsRepo;
		$this->apiResponse = $apiResponse;
    }

    public function addAutoPartsBasicInfo($request)
    {
        $data = $request->post();
    // Validate request parameters
    $missingParams = Validator::validateRequiredParams($this->apiAddAutoPartsBasicInfoParams, $data);
    if(!empty($missingParams))
    {
    $errors = Validator::convertToRequiredValidationErrors($missingParams);
    ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

    return $this->apiResponse->validationError(
        ["errors" => $errors]
    );
    }


    // Validate part condition value
    if($data['condition'] != "NEW" && $data['condition'] != "USED")
    {
        $message = "Validation error occurred on the condition";
        ErrorEvents::apiErrorOccurred($message);
        return $this->apiResponse->validationError(
            ["errors" => ["condition" => "condition parameter must be a value of  'NEW' or 'USED'"]]
        );
    }

    //check if carModel does not exist
    if(!$this->autoPartsRepo->getCarModelById($data['model_id'])){
        $message = "The specified carmodel does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
    }

    //check if subcategory does not exist
    if(!$this->autoPartsRepo->getSubCategoryById($data['subcategory_id'])){
        $message = "The specified subcategory does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
        }

    //check if seller does not exist
    if(!$this->autoPartsRepo->getSellerById($data['seller_id'])){
        $message = "The specified Seller does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
        }

    //   // Check if autopart  name exists if name is specified

    //       if($this->autoPartsRepo->autoPartsExists($data['name']))
    //       {
    //           $message = "The specified name {$data['name']} already exists";
    //           ErrorEvents::apiErrorOccurred($message, "warning");
    //           return $this->apiResponse->generalError($message);
    //       }


        $addAutoPart= $this->autoPartsRepo->createAutoPart($data);
        if($addAutoPart)
        {
            $message = "AutoPart : {$data['name']} added successfully";
            AuditEvent::logEvent($request,$addAutoPart,$message);
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $addAutoPart->toArray()] );
        }
        else
        {
            $message = "Unable to add autoPart{$data['name']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
                    }


    //editing and updating Autoparts
    public function updateAutoParts($request)
    {
        $data = $request->post();

    // Validate request parameters
    $missingParams = Validator::validateRequiredParams($this->apiEditAutoPartsParams, $data);
    if(!empty($missingParams))
    {
    $errors = Validator::convertToRequiredValidationErrors($missingParams);
    ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

    return $this->apiResponse->validationError(
        ["errors" => $errors]
    );
    }

    // Validate part condition value
    if($data['condition'] != "NEW" && $data['condition'] != "USED")
    {
        $message = "Validation error occurred on the condition";
        ErrorEvents::apiErrorOccurred($message);
        return $this->apiResponse->validationError(
            ["errors" => ["condition" => "condition parameter must be a value of  'NEW' or 'USED'"]]
        );
    }

        //check if carModel does not exist
        if(!$this->autoPartsRepo->getCarModelById($data['model_id'])){
        $message = "The specified carmodel does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
    }

    //check if subcategory does not exist
    if(!$this->autoPartsRepo->getSubCategoryById($data['subcategory_id'])){
        $message = "The specified subcategory does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
        }

    //check if seller does not exist
    if(!$this->autoPartsRepo->getSellerById($data['seller_id'])){
        $message = "The specified Seller does not exists";
        ErrorEvents::apiErrorOccurred($message, "warning");
        return $this->apiResponse->notFoundError($message);
        }
        
            $auto_part = $this->autoPartsRepo->getAutoPartById($data['auto_part_id']);

            $editAutoPart= $this->autoPartsRepo->updateAutoPart($data, $auto_part->id);
            if($editAutoPart)
            {
                $message = "AutoPart : {$data['name']} Updated successfully";
                
                AuditEvent::logEvent($request,$editAutoPart,$message);

                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $editAutoPart->toArray()] );
            }
            else
            {
                $message = "Unable to add autoPart{$data['name']}";
                ErrorEvents::apiErrorOccurred($message);
                return $this->apiResponse->generalError($message);
            }
    }


    public function autoPartSearch($data)
    {
        // Attempt to search Parts
        $searchAutoPart= $this->autoPartsRepo->searchAutoParts($data);
        if($searchAutoPart)
        {
            $message = "Autoparts : search results";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $searchAutoPart] );
        }
        else
        {
            $message = "Unable to fetch data";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }
    }


    public function addImage(Array $data)
    {
        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiAddImageParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }

        // Upload Autoparts Image
        $autoPartsImage = $this->autoPartsRepo->uploadImage($data);
        if($autoPartsImage)
        {
            $message = "Image Uploaded Successfully";
            Log::notice($message);
            $response_data['image_url'] = url("/system/autopart/image/{$autoPartsImage->name}");
            return $this->apiResponse->success($message, ["data" => $response_data]);
        }

        $errMsg = "Could not upload image";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }


                //editing and updating Autoparts image
                public function updateAutoPartImage($data)
                {
                // Validate request parameters
                $missingParams = Validator::validateRequiredParams($this->apiEditAutoPartImageParams, $data);
                if(!empty($missingParams))
                {
                $errors = Validator::convertToRequiredValidationErrors($missingParams);
                ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));
    
                return $this->apiResponse->validationError(
                    ["errors" => $errors]
                );
                }
    
                $auto_part_image = $this->autoPartsRepo->getPartImageByName($data['part_image_name']);
    
                        //check if auto part image does not exist
                    if(!$auto_part_image){
                        $message = "The specified image does not exists";
                        ErrorEvents::apiErrorOccurred($message, "warning");
                        return $this->apiResponse->notFoundError($message);
                    }
    
                $editAutoPartImage= $this->autoPartsRepo->updateAutoPartImage($data, $auto_part_image->id);
                if($editAutoPartImage)
                {
                    $message = "AutoPart image : {$data['part_image_name']} Updated successfully";
                    Log::notice($message);
                    $response_data['image_url'] = url("/system/autopart/image/{$auto_part_image->name}");
                    return $this->apiResponse->success($message, ["data" => $response_data] );
                }
                else
                {
                    $message = "Unable to add autoPartImage{$data['part_image_name']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
            }



        public function getAutoParts($filters)
        {
            // fetching Autoparts
            $autoParts = $this->autoPartsRepo->getAllAutoParts($filters);
            if($autoParts)
            {
                $message = "AutoParts Data results";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $autoParts]);
            }

            $errMsg = "Could not get AutoParts";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }

        public function getAutoPartsWeb()
        {
            // fetching Autoparts
            $autoPartsWeb = $this->autoPartsRepo->getAllWebAutoParts();
            if($autoPartsWeb)
            {
                $message = "AutoParts Data results";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $autoPartsWeb]);
            }

            $errMsg = "Could not get AutoParts";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }

    public function removeImage($data)
    {
                // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiDeleteImageParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
        log::notice($data);
        $image = $this->autoPartsRepo->getImageById($data['image_id']);
        
        $RemoveImage= $this->autoPartsRepo->deleteImage($data);
        if($RemoveImage)
        {
            $message = "Image : {$data['image_id']} deleted successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $RemoveImage->toArray()] );
        }
        else
        {
            $message = "Unable to delete for {$data['image_id']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

    }

    public function getAutoSubCategories($filters, $uid)
    {
        $subcategory = $this->subCategoryRepo->getSubCategoryByUid($uid);
        //check if category exist
        if(!$subcategory){
            $message = "The specified subcategory does not exist";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->generalError($message);
        }

        $autoParts = $this->autoPartsRepo->getAllAutoSubCategories($filters, $subcategory->id);
        if($autoParts)
        {
            $message = "Auto Parts results";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $autoParts]);
        }

        $errMsg = "Could not get Auto Parts";
        ErrorEvents::apiErrorOccurred($errMsg);
        return $this->apiResponse->generalError($errMsg);
    }


    public function removeSpecs($request)
    {
        $data = $request->post();

        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiDeleteSpecsParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
        log::notice($data);
        $specification = $this->autoPartsRepo->getSpecsById($data['specification_id']);

        if(!$specification){
            $message = "The specified specification does not exist";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->generalError($message);
            }
        
        
        
        $RemoveSpecs= $this->autoPartsRepo->deleteSpecs($data);
        if($RemoveSpecs)
        {
            $message = "Specification : {$specification['key']} deleted successfully";
            
            AuditEvent::logEvent($request,$RemoveSpecs,$message);

            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $RemoveSpecs->toArray()] );
        }
        else
        {
            $message = "Unable to delete for {$specification['key']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

    }

    public function reStockPart($request)
    {
        $data = $request->post();

        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiRestockAutoPartsParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
        log::notice($data);
        $auto_part = $this->autoPartsRepo->getAutoPartById($data['auto_part_id']);

        if(!$auto_part){
            $message = "The specified auto part does not exist";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->generalError($message);
            }
        
        
        
        $reStockPart= $this->autoPartsRepo->partRestock($data);
        if($reStockPart)
        {
            $message = "Specification : part restocked successfully";
            
            AuditEvent::logEvent($request,$reStockPart,$message);

            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $reStockPart->toArray()] );
        }
        else
        {
            $message = "something went wrong restocking auto-part";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

    }

    public function deleteAutoParts($request)
    {
        $data = $request->post();
                // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiDeleteAutoPartParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
        log::notice($data);
        $parts = $this->autoPartsRepo->getAutoPartById($data['part_id']);

        if(!$parts){
            $message = "The specified part does not exist";
            ErrorEvents::apiErrorOccurred($message, "warning");
            return $this->apiResponse->generalError($message);
            }
        
        $autoParts= $this->autoPartsRepo->deleteAutoParts($data);
        if($autoParts)
        {
            $message = "AutoPart : {$parts['name']} deleted successfully";
            AuditEvent::logEvent($request,$autoParts,$message);
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $autoParts->toArray()] );
        }
        else
        {
            $message = "Unable to delete for {$data['part_id']}";
            ErrorEvents::apiErrorOccurred($message);
            return $this->apiResponse->generalError($message);
        }

    }

    public function publishAutoPart($request)
    {
        $data = $request->post();
                // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiAutoPartPublishParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }
        log::notice($data);
        $auto_part = $this->autoPartsRepo->getAutoPartById($data['auto_part_id']);
        
        $publishPart= $this->autoPartsRepo->changePartStatus($data['auto_part_id']);
        if($auto_part->status == Constants::STATUS_DISABLED){
            $message = " Auto Part {$auto_part['name']} Enabled successful";
        }else{
            $message = " Auto Part {$auto_part['name']}  Disabled successful";
        }
        
        AuditEvent::logEvent($request,$publishPart,$message);

        Log::notice($message);
        return $this->apiResponse->success($message);
    }





    public function addAutoPartSpecifications($request)
    {
        $data = $request->post();

        // Validate request parameters
        $missingParams = Validator::validateRequiredParams($this->apiAddSpecificationParams, $data);
        if(!empty($missingParams))
        {
            $errors = Validator::convertToRequiredValidationErrors($missingParams);
            ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

            return $this->apiResponse->validationError(
                ["errors" => $errors]
            );
        }

                        //check if AutoPart does not exist
            if(!$this->autoPartsRepo->getAutoPartById($data['auto_part_id'])){
                $message = "The specified AutoPart does not exists";
                ErrorEvents::apiErrorOccurred($message, "warning");
                return $this->apiResponse->notFoundError($message);
            }

                $addAutoPartSpecs= $this->autoPartsRepo->createSpecifications($data);
                if($addAutoPartSpecs)
                {
                    $message = "AutoPartSpecicification : {$data['key']} added successfully";
                    
                    AuditEvent::logEvent($request,$addAutoPartSpecs,$message);

                    Log::notice($message);
                    return $this->apiResponse->success($message, ["data" => $addAutoPartSpecs->toArray()] );
                }
                else
                {
                    $message = "Unable to add AutoPartSpecicification {$data['key']}";
                    ErrorEvents::apiErrorOccurred($message);
                    return $this->apiResponse->generalError($message);
                }
        }


        public function viewAutoPart($profile)
        {
                // Validate request parameters
            $missingParams = Validator::validateRequiredParams($this->apiAutoPartParams, $profile);
            if(!empty($missingParams))
            {
                $errors = Validator::convertToRequiredValidationErrors($missingParams);
                ErrorEvents::apiErrorOccurred("Validation error, " . join(";", $errors));

                return $this->apiResponse->validationError(
                    ["errors" => $errors]
                );
            }

            // fetching autopart
            $autopart = $this->autoPartsRepo->getAnAutoPartByid($profile['auto_part_id']);

                    //Check if part exist
            if(!$autopart){
                $message = "The auto part does not exist";
                ErrorEvents::apiErrorOccurred($message);
                return $this->apiResponse->notFoundError($message);

            }
            if($autopart)
            {
            //    // foreach($autopart as $part){
            //        unset($autopart['access_token']);
            //        unset($autopart['session_id']);
            //        unset($autopart['session_id_time']);
            //        unset($autopart['last_logged_in']);
            //    // }
                $message = "Part Details";
                Log::notice($message);
                return $this->apiResponse->success($message, ["data" => $autopart]);
            }

            $errMsg = "Could not get any Part";
            ErrorEvents::apiErrorOccurred($errMsg);
            return $this->apiResponse->generalError($errMsg);
        }

        public function mGetHomeMoreAutoPartsGrouped($filters)
        {
            $message = "Home more autoparts retrieved successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $this->autoPartsRepo->mGetMoreAutoParts($filters)]);
            
        }

        public function mGetHomeMostPopularAutoPartsGrouped($filters)
        {
            
            $message = "Home popular autoparts retrieved successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $this->autoPartsRepo->mGetPopularAutoParts($filters)]);
            
        }

        public function mGetHomeNewestArrivalsAutoPartsGrouped($filters)
        {

            $message = "Home newest arrivals autoparts retrieved successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $this->autoPartsRepo->mGetNewestArrivalAutoParts($filters)]);
            
        }

        public function mGetRelatedAutopartList($filters,$id)
        {
            $auto_part = $this->autoPartsRepo->getAutoPartByid($id);
            if(!$auto_part){
                $message = "Autopart not found";
                return $this->apiReponse->notFoundError($message);
            }

            $message = "Related autoparts retrieved successfully";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $this->autoPartsRepo->mGetRelatedAutopartList($filters,$auto_part)]);
        }


        // public function viewGrossingPartItems($filters)
        // {
    
        //     $topGrossing = $this->autoPartsRepo->viewGrossingPartItems($filters);
           
        //     $message = "Top grossing items results";
        //     Log::notice($message);
        //     return $this->apiResponse->success($message, ["data" => $topGrossing]);
            
    
        //     // $errMsg = "Could not get results";
        //     // ErrorEvents::apiErrorOccurred($errMsg);
        //     // return $this->apiResponse->generalError($errMsg);
        // }

        public function revenueStats($filters)
        {
    
            $topGrossing = $this->autoPartsRepo->revenueStats($filters);
           
            $message = "Showing Statistics on Revenue generated";
            Log::notice($message);
            return $this->apiResponse->success($message, ["data" => $topGrossing]);
            
        }

        

}