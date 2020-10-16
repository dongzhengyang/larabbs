<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KsUser extends Model
{
     
     protected $table = 'user_data';
     
     public $timestamps = false;
     
      protected $fillable = [
        'user_id', 'flag','total_mil'
    ];
}
