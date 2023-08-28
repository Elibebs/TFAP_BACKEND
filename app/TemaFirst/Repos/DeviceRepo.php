<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\TemaFirst\Utilities\Generators;
use Illuminate\Support\Facades\Log;
use App\TemaFirst\Utilities\Constants;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\AutoPart;
use App\Activity;



class DeviceRepo extends AuthRepo
{

    public function getPlayerId(Array $data, $identifier)
    {
        $player_id  = $this->getIdentifier($identifier);
    }

    public function getDevice($identifier){

        $device = Device::where("identifier", $identifier)->first();
          if(!$device){
             $device = $this->createDevice($identifier);
          }
          return $device;
    }

    public function createDevice($identifier){
        $device = new Device;
        $device->player_id = $this->getNewPlayerId();
        $device->identifier = $identifier;
        $device->created_at = Carbon::now();
        $device->updated_at = Carbon::now();

        if($device->save())
            return $device;
        else
            return null;
    }

    private function getNewPlayerId(){
        $player_id_exist = true;
        $player_id = null;
        while($player_id_exist){
            $player_id = Generators::generateUniq();
            $device = Device::where('player_id',$player_id)->first();
            if(!$device) $player_id_exist = false;
        }

        return $player_id;
    }
}