<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\SubCategoryActivity;

use Redirect;
use Session;
use Excel;

class SubCategoriesController extends Controller
{

    protected $subCategoryActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        SubCategoryActivity $subCategoryActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->subCategoryActivity = $subCategoryActivity;
    }

    public function addSubCategory(Request $request)
    {
        try
        {
            return  $this->subCategoryActivity->addNewSubCategory($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function editSubCategory(Request $request)
    {
        try
        {
            return  $this->subCategoryActivity->updateSubCategory($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function getPartsGroup(Request $request)
    {
        try 
        {
            return $this->subCategoryActivity->getPartsGrouped($request->post());
        } 
        catch(\Exception $e) 
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function deleteSubCategory(Request $request)
    {
        try
        {
            return  $this->subCategoryActivity->removeSubCategory($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listSubCategory(Request $request){

        try
        {
            return  $this->subCategoryActivity->getSubCategories($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }


    public function mlistCategorySubcatories(Request $request, $uid)
    {
        try
        {
            return  $this->subCategoryActivity->mGetCategorySubcategories($request->post(),$uid);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }


    public function getCategorySubcats(Request $request, $uid){

        try
        {
            return  $this->subCategoryActivity->getCategorySubCategories($uid);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }


    public function addSubCategoryImage(Request $request)
    {
        try
        {
            return  $this->subCategoryActivity->addImage($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function subCategoryAutoPartDetails(Request $request, $uid){
        try
        {
            return  $this->subCategoryActivity->subCategoryAutoPartDetails($request->post(), $uid);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

}
