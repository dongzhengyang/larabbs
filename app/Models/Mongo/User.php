<?php

namespace App\Models\Mongo;

use App\Models\Mongo\Model as Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $collection = 'users';
    protected $fillable = [
        'user_id',
        'account',
        'password',
        'nickname',
        'gender',
        'area_id',
        'height',
        'weight',
        'birthday',
        'picture',
        'phone',
        'email',
        'year_ranking',
        'year_integral',
        'private_target',
        'private_set',
        'official_target',
        'target_mode',
        'store_id',
        'private_area',
        'post_permission',
        'allow_friend_add',
        'notification_like',
        'notification_communicate',
        'notification_friend',
        'notification_motorcade',
        'coordinate',
        'create_time',
        'create_user',
        'update_time',
        'update_user',
        'user_type',
        'oauth2',
        'cycling_statistics'
    ];
}

