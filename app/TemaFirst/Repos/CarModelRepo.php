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
use App\Models\CarYear;
use App\Activity;
use App\Exports\SystemUsersExport;
use App\Exports\ExportAuditTrail;
use App\Exports\RolesExport;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class CarModelRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(CarModel $car_model)
    {
        $this->model = $car_model;
    }

    public function createCarModel(Array $data)
    {
    	$car_model = new CarModel;

        $car_model->name = $data['name'];
        $car_model->make_id = $data['make_id'];
        $car_model->year_id = $data['year_id'];
        // $car_model->UID =  Generators::generateUniq();


    	if($car_model->save())
    	{
    		return $car_model;
    	}
    	return null;
    }

    public function updateCarModel($data, $id)
    {
        $car_model = CarModel::where("id", $id)->first();

        $car_model->name = $data['name'];
        $car_model->make_id = $data['make_id'];
        $car_model->year_id = $data['year_id'];


        if($car_model->update())
            return $car_model;
        else
            return null;
    }


 public function carModelExists($name, $year)
 {
    $car_model = CarModel::where([['name',$name],['year_id' ,$year]])->first();
    if($car_model)
        return true;
    else
        return false;
}

public function getCarModelById($id)
{
    return CarModel::where("id", $id)->first();
}

public function getCarMakeById($id)
{
    return CarMake::where("id", $id)->first();
}

public function getCarYearById($id)
{
    return CarYear::where("id", $id)->first();
}

public function deleteCarModel($id)
{
    $car_model = CarModel::where("id", $id)->first();
    $car_model->delete();

    return $car_model;
}

public function getAllCarModels($car_model){
  
    $car_model=CarModel::with('carYearName')->get();

    return $car_model;
    
}

public function searchMakeModelYear(Array $data){


    $search=$data['search']??null;

    $query= CarModel::query();

    $query->with(array('carYear'=>function($query){
        $query->select('name', 'id');
    }));
    $query->with(array('carMake'=>function($query){
        $query->select('title', 'id');
    }));
   // $query->with('carYear');

    if(isset($search)){
        $query->whereHas('carYear', function ($query) use ($search){
                    $query->where('name', 'ilike', '%'.$search.'%');})
                  ->orWhereHas('carMake', function ($query) use ($search){
                    $query->where('title', 'ilike', '%'.$search.'%');})
                 ->orWhere('name', 'ilike', '%'.$search.'%');
    }


    $make_model_years = $query->orderBy('name', 'asc')->get();

        return $make_model_years;
}

}