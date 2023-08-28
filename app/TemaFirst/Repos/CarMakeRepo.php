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
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\SubCategory;
use App\Activity;
use App\Exports\SystemUsersExport;
use App\Exports\ExportAuditTrail;
use App\Exports\RolesExport;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class CarMakeRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(CarMake $carMake)
    {
        $this->model = $carMake;
        // $this->model = $subcategory;

    }


    public function createCarMake(Array $data)
    {
        $carmake = new CarMake;
        
        $carmake->title = $data['title'];
        $carmake->description = $data['description'];
        $carmake->UID =  Generators::generateUniq();
    	// $carmake->created_at = Carbon::now();
        // $carmake->updated_at = Carbon::now();

    	if($carmake->save())
    	{
    		return $carmake;
    	}
    	return null;
    }

    public function updateCarMakeInfo($data, $id)
    {
        $carmake = CarMake::where("id", $id)->first();

        $carmake->title = $data['title'];
        $carmake->description = $data['description'];


        if($carmake->update())
            return $carmake;
        else
            return null;
    }


 public function carMakeExists($title)
 {
    $carmake = CarMake::where('title',$title)->first();
    if($carmake)
        return true;
    else
        return false;
}

public function getCarMakeById($id)
{
    return CarMake::where("id", $id)->first();
}

public function deleteCarMake($id){

    $carmake = CarMake::where('id',$id)->first();
    return $carmake->delete();
}

public function getAllCarMakes($carmakes){
  
    $carmakes=CarMake::all();

    return $carmakes;
    
}

public  function listMakeWithModels($filters)
{
    $pageSize = $filters['pageSize'] ?? 20;
    $predicate = CarModel::query();
    foreach ($filters as $key => $filter) {
        if(in_array($key, Constants::FILTER_PARAM_IGNORE_LIST))
        {
            continue;
        }

        $predicate->where($key, $filter);
    }

    if(isset($filters["search_text"]))
    {
        $searchText = "%".$filters["search_text"]."%";

        $predicate->join('activities.make',"activities.make.id","=","parts.model.make_id","full")
                  ->join("parts.year","parts.year.id","=","parts.model.year_id","left")
                  ->where("activities.make.title","ilike",$searchText)
                  ->orWhere("parts.model.name","ilike",$searchText)
                  ->orWhere("parts.year.name","=",$filters["search_text"]);
    }else{
        $predicate->join('activities.make',"activities.make.id","=","parts.model.make_id","full")->join("parts.year","parts.year.id","=","parts.model.year_id","left");
    }

    $predicate->select("activities.make.id as makeid", "activities.make.title as make","parts.model.id as modelid","parts.model.name as model","parts.year.id as yearid","parts.year.name as year",);

    return $predicate->paginate($pageSize);
}

}