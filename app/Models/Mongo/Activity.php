<?php

namespace App\Models\Mongo;

use App\Models\Mongo\Model as Model;

class Activity extends Model
{
    protected $collection = 'activities';
    
    protected $fillable = [
        'no',
        'name',
        'time_beg',
        'time_end',
        'sync',
        'participants',
        'created_at',
        'updated_at',
        'status'
    ];
}
