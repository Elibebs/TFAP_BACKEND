<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
	protected $primaryKey = "verification_id";
    protected $table = "auth.verifications";
    protected $dateFormat = 'Y-m-d H:i:sO';
}
