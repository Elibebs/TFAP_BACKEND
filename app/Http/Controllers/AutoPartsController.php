<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\AutoPartsActivity;

use Redirect;
use Session;
use Excel;

class AutoPartsController extends Controller
{

    protected $autoPartsActivity;
    protected $apiResponse;

	public function __construct(
        ApiResponse $apiResponse,
        AutoPartsActivity $autoPartsActivity
	)
    {
        $this->apiResponse = $apiResponse;
    	$this->autoPartsActivity = $autoPartsActivity;
    }

    public function addBasicAutoPartInfo(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->addAutoPartsBasicInfo($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function editBasicAutoPartInfo(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->updateAutoParts($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function addAutoPartImage(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->addImage($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }
    public function updateAutoPartImage(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->updateAutoPartImage($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function reStockPart(Request $request){
        try
        {
            return  $this->autoPartsActivity->reStockPart($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteImage(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->removeImage($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function deleteSpecs(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->removeSpecs($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function deleteAutoParts(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->deleteAutoParts($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function listAutoParts(Request $request){

        try
        {
            return  $this->autoPartsActivity->getAutoParts($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

   /**
    * Functionality moved to Dashboard controller, delete afterwards
    */
    public function viewGrossingPartItems(Request $request){

        try
        {
            return  $this->autoPartsActivity->viewGrossingPartItems($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    /**
    * Functionality moved to Dashboard controller, delete afterwards
    */
    public function revenueStats(Request $request){

        try
        {
            return  $this->autoPartsActivity->revenueStats($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function getAutoSubcats(Request $request, $uid){

        try
        {
            return  $this->autoPartsActivity->getAutoSubCategories($request->post(), $uid);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }

    public function viewAutoPart(Request $request){

        try
        {
            return  $this->autoPartsActivity->viewAutoPart($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }
    


    public function listWebAutoParts(Request $request){

        try
        {
            return  $this->autoPartsActivity->getAutoPartsWeb($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
        
    }


    public function addSpecifications(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->addAutoPartSpecifications($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function searchAutoParts(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->autoPartSearch($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }


    public function publishAutoParts(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->publishAutoPart($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function mGetHomeMoreAutoPartsGrouped(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->mGetHomeMoreAutoPartsGrouped($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function mGetHomeMostPopularAutoPartsGrouped(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->mGetHomeMostPopularAutoPartsGrouped($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function mGetHomeNewestArrivalsAutoPartsGrouped(Request $request)
    {
        try
        {
            return  $this->autoPartsActivity->mGetHomeNewestArrivalsAutoPartsGrouped($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function mGetRelatedAutopartList(Request $request,$uid)
    {
        try
        {
            return  $this->autoPartsActivity->mGetRelatedAutopartList($request->post(),$uid);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

}
