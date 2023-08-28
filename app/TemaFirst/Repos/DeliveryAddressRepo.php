<?php

namespace App\TemaFirst\Repos;

use Carbon\Carbon;
use App\Models\Permission;
use App\TemaFirst\Utilities\Generators;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\TemaFirst\Utilities\Constants;
use App\Mail\WelcomeMail;
use App\Notifications\WelcomeUser;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\User;
use App\Activity;
use Illuminate\Notifications\Notifiable;


use Excel;
use Redirect;
use Session;


class DeliveryAddressRepo extends AuthRepo
{
    use Notifiable;

	public function __construct(Address $address)
    {
        $this->model = $address;

    }


    public function addAddress(Array $data,$user_id)
    {
        $address = new Address;

        $address->name = $data['name'];
        $address->address = $data['address'];
        $address->customer_id = $user_id;
        $address->phone_number = $data['phone_number'];
        $address->region = $data['region'];
        $address->town = $data['town'];

        $user_addresses = Address::where([['customer_id',$user_id],['status' ,'true']])->get();
        if(count($user_addresses)> 0){
            $address->status =  Constants::ADDRESS_STATUS_INACTIVE; 
        }
        else{
            $address->status =  Constants::ADDRESS_STATUS_DEFAULT;
        }
        $address->additional_info = $data['additional_info']??null;

    	if($address->save())
    	{
    		return $address;
    	}
    	return null;
    }

    public function updateDeliveryAddressInfo($data, $id)
    {
        $address = Address::where("id", $id)->first();

        if(!$address)
            return null;

        if(isset($data['name'])){$address->name = $data['name'];}
        if(isset($data['address'])){$address->address = $data['address'];}
        if(isset($data['phone_number'])){$address->phone_number = $data['phone_number'];}
        if(isset($data['region'])){$address->region = $data['region'];}
        if(isset($data['town'])){$address->town = $data['town'];}
        if(isset($data['additional_info'])){$address->additional_info = $data['additional_info'];}

        if($address->update())
            return $address;
        else
            return null;
    }

    public function changeAddressStatus($id, $user_id)
    {
        $addresses = Address::where([['customer_id',$user_id],['id',$id],['status' , 'false']])->first();
        if($addresses){
            $addresses->status = 'true';
            $addresses->update();
        }
        $user_addresses = Address::where('customer_id', $user_id)->get();

        foreach($user_addresses as $user_address){
            if($user_address->id != $id){
                $user_address->status = 'false';
                $user_address->update();
            }
        }
        return $user_addresses;

    }

    public function viewDeliveryAddresses($data, $id){
        $addresses = Address::where("customer_id", $id)->get();

        return $addresses;
    }

    public function getAllDeliveryAddresses($user_id){
        return Address::where('customer_id',$user_id)->orderBy('name','DESC')->paginate(20);
    }
    

    public function deleteAddress($id){

        $address = Address::where('id',$id)->first();
        return $address->delete();
    }

public function getClientById($user_id)
{
    return User::where("user_id", $user_id)->first();
}

public function getUserProfileById($user_id)
{
    return User::where("user_id", $user_id)->first();
}

public function getAddressById($id)
{
    return Address::where("id", $id)->first();
}

}