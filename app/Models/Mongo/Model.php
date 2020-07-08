<?php

namespace App\Models\Mongo;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\DB;

abstract class Model extends Eloquent
{
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        if (DB::getDefaultConnection() != config('database.connections.mongodbOneHost.database')) {
            $this->connection = config('database.default_mongodb');
        }
    }
}

