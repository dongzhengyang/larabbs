<?php

namespace App\Models\Mongo;

use App\Models\Mongo\Model as Model;

class UserMedal extends Model
{
    protected $table = 'user_medals';
    
    protected $fillable = [ 
        'id', 
        'user_id',
        'medal_id',
        'records',
        'unread',
        'number_of_new_record',
        'created_at' ,
        'updated_at' ,
        'light'
    ];
}
