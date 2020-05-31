<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
     use Traits\MileageSortHelper;
     
     protected $table = 'user_data';
     
     public $timestamps = false;
     
      protected $fillable = [
        'mileage', 'mongo_user_id','user_id','sort',
    ];
}
