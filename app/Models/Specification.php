<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPassword as ResetPasswordNotification;


class Specification extends Model
{
    use Notifiable;

	protected $primaryKey = "id";
    protected $table = "parts.specifications";
    public $timestamps = false;

    public function autoPart()
    {
    	return $this->belongsTo('App\Models\AutoPart', 'auto_part_id');
    }


}
