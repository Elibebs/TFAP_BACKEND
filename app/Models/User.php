<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class User extends Model
{
	protected $primaryKey = "user_id";
    protected $table = "client.customer";
    protected $dateFormat = 'Y-m-d H:i:sO';



    public function Address()
    {
    	return $this->hasMany('App\Models\Address', 'user_id');
    }
    public function cart()
    {
    	return $this->hasMany('App\Models\Cart', 'user_id');
    }

}
