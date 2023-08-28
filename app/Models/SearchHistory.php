<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SearchHistory extends Model
{

	protected $primaryKey = "id";
    protected $table = "activities.search_history";
    public $timestamps = false;

}
