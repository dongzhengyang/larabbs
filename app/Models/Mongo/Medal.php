<?php

namespace App\Models\Mongo;

use App\Models\Mongo\Model as Model;

class Medal extends Model
{
    protected $table = 'medals';
    
    protected $fillable = [ 
        'name', 
        'type',
        'description',
        'images',
        'conditions',
        'point',
        'visible_from' ,
        'visible_to' ,
        'id',
        'activity_no',
        'act_id'
    ];
}
