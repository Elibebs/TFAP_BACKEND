<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Validator;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\CarYear;
use App\Models\AutoPart;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;



class SearchActivity extends BaseActivity
{
	protected $apiResponse;

	public function __construct(ApiResponse $apiResponse)
    {
		$this->apiResponse = $apiResponse;
    }

    public function getSuggestions($filters){
        
        $suggestions = [];
        $suggestions = Category::get(["name"]);
        $suggestions = $suggestions->concat(SubCategory::get(["name"]));
        $suggestions = $suggestions->concat(CarMake::select('title as name')->get());
        $suggestions = $suggestions->concat(CarModel::get(["name"]));
        $suggestions = $suggestions->concat(CarYear::get(["name"]));

        $recent_search_excempt = [];
        foreach($suggestions as $suggestion)
        {
          array_push($recent_search_excempt,$suggestion->name);
        }

        $recent_searches = SearchHistory::distinct()->select('keyword as name')->whereNotIn('keyword',$recent_search_excempt)->get();

        $suggestions = $suggestions->concat($recent_searches);
        
        $message = "Search suggestions retrieved successfully";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $suggestions]);
    }

    public function search($request)
    {
      $filters = $request->post();

      $pageSize = $filters['pageSize'] ?? 20;
      $predicate = AutoPart::query();
      foreach ($filters as $key => $filter) {
          if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
          {
              continue;
          }
  
          $predicate->where($key, $filter);
      }

      if(isset($filters["search_text"])){
          $search_text = $filters["search_text"];
          $search_text_pattern =  '%'.$filters["search_text"].'%';

          //Get model ids base on car make and car year
          $models = CarModel::where('name','ilike',$search_text)
                              ->orWhereHas('carMake',function($query) use ($search_text,$search_text_pattern){
                                $query->where('title','ilike',$search_text_pattern);
                              })
                              ->orWhereHas('carYear',function($query) use ($search_text){
                                $query->where('name',$search_text);
                              })->select('id')->get()->toArray();

      
          //Get base n categories, subcategories, models etc
          $predicate->where('name','ilike',$search_text_pattern)
                    ->orWhere('condition','ilike',$search_text)
                    ->orWhereHas('subCategory',function($query) use ($search_text_pattern){
                      $query->where('name','ilike',$search_text_pattern);
                    })
                    ->orWhereIn('model_id',$models);
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

      //Save search text
      $this->saveSearch($request,$filters["search_text"]);
  
      return $auto_parts;
    }

    public function getRecentSearchList($request)
    {
      $filters = $request->post();

      $pageSize = $filters['pageSize'] ?? 20;
      $predicate = SearchHistory::query();
      foreach ($filters as $key => $filter) {
          if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
          {
              continue;
          }
  
          $predicate->where($key, $filter);
       }

       $deviceId = $request->headers->get('device-id');   
       $customer_id = $request->headers->get('customer-id'); 

       if(isset($deviceId))
          $predicate->where('device_id',$deviceId);
        if(isset($customer_id))
          $predicate->where('customer_id',$customer_id);
        
      $recentSearches = $predicate->distinct()->select('keyword')->paginate($pageSize);
      
      $message = "Recent search retrieved successfully";
      Log::notice($message);
      return $this->apiResponse->success($message, ["data" => $recentSearches]);
  }

    function saveSearch(Request $request, $keyword)
    {

      $deviceId = $request->headers->get('device-id');   
      $customer_id = $request->headers->get('customer-id'); 

      if(isset($deviceId))
      {
        $searchHistory = new SearchHistory;
        $searchHistory->keyword = $keyword;
        $searchHistory->device_id = $deviceId;
        $searchHistory->customer_id = $customer_id??null;
        $searchHistory->created_at = Carbon::now();
        $searchHistory->updated_at = Carbon::now();

        $searchHistory->save();

      }

    }
}