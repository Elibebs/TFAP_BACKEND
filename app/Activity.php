<?php

namespace App;


class Activity extends \Spatie\Activitylog\Models\Activity
{
    protected $primaryKey = "id";
    protected $table = "activity_log";
    protected $dateFormat = 'Y-m-d H:i:s';

   // protected static $logAttributes = ['id','name','created_at'];
    protected static $logName = 'system user Audit Trails';

}
