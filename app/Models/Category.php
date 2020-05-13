<?php

namespace App\Models;

class Category extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name','description',
    ];
    
}
