<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use App\TemaFirst\Api\ApiResponse;
use App\TemaFirst\Events\ErrorEvents;
use Illuminate\Http\Request;
use App\TemaFirst\Activities\SearchActivity;

class SearchController extends Controller
{

    protected $apiResponse;
    protected $searchActivity;

	public function __construct(ApiResponse $apiResponse,SearchActivity $searchActivity)
    {
        $this->apiResponse = $apiResponse;
        $this->searchActivity = $searchActivity;
    }

    public function index(Request $request)
    {
        try
        {
            return  $this->searchActivity->search($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

    public function getSuggestions(Request $request)
    {
        try
        {
            return  $this->searchActivity->getSuggestions($request->post());
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }

    }

    public function getRecentSearchList(Request $request)
    {
        try
        {
            return  $this->searchActivity->getRecentSearchList($request);
        }
        catch(\Exception $e)
        {
            ErrorEvents::ServerErrorOccurred($e);
            return $this->apiResponse->serverError();
        }
    }

}
