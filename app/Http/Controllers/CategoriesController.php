<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\CategoryActivity;

use Redirect;
use Session;
use Excel;

class CategoriesController extends Controller
{

    protected $categoryActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        CategoryActivity $categoryActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->categoryActivity = $categoryActivity;
    }

    public function addCategory(Request $request)
    {
        try
        {
            return  $this->categoryActivity->addNewCategory($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function editCategory(Request $request)
    {
        try
        {
            return  $this->categoryActivity->updateCategory($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteCategory(Request $request)
    {
        try
        {
            return  $this->categoryActivity->removeCategory($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function getCategorySubcategoriesData(Request $request){

        try
        {
            return  $this->categoryActivity->getSubCategoriesData($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function listCategory(Request $request){

        try
        {
            return  $this->categoryActivity->getCategories($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function mlistCategories(Request $request){

        try
        {
            return  $this->categoryActivity->mGetCategories($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function addCategoryImage(Request $request)
    {
        try
        {
            return  $this->categoryActivity->addImage($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function listCategoryItems(Request $request)
    {
        try
        {
            return  $this->categoryActivity->listCategoryItems($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteCategoryImage(Request $request)
    {
        try
        {
            return  $this->categoryActivity->removeCategoryImage($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function searchCategory(Request $request)
    {
        try
        {
            return  $this->categoryActivity->searchCategory($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

}
