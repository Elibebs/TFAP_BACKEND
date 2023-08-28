<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\CarYear;
use App\TemaFirst\Utilities\Constants;



class YearRepo extends AuthRepo
{
    
    public function yearlist()
    {
        return CarYear::get();
    }


    public function createYear(Array $data)
    {
        $year = new CarYear;
        
        $year->name = $data['name'];
        //$year->description = $data['description'];
        //$year->UID =  Generators::generateUniq();
    	// $year->created_at = Carbon::now();
        // $year->updated_at = Carbon::now();

    	if($year->save())
    	{
    		return $year;
    	}
    	return null;
    }

        public function getCarYearById($id)
    {
        return CarYear::where("id", $id)->first();
    }



    public function deleteCarYear($id){

        $year = CarYear::where('id',$id)->first();
        return $year->delete();
      }


 public function yearExists($name)
 {
    $year = CarYear::where('name',$name)->first();
    if($year)
        return true;
    else
        return false;
}

public function listCarYears($filters){
   $years = CarYear::get();

    return $years;
}

}