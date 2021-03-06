<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ActivityParticipant;
use Carbon\Carbon;

class Activity extends Model
{
    
    protected $table = 'activity';
    
    const STATUS_ON = 1;
    const STATUS_OFF = 0;
    
    protected $dates = [
        'create_time','update_time','time_begin','time_end'
    ];
    
    protected $fillable = [
        'activity_number','name', 'time_begin', 'time_end',
        'part_count', 'activity_count','topic_count', 'mongo_activity_id',
        'create_time','update_time'
    ];
    
    public function participants() : HasMany
    {
        return $this->hasMany(ActivityParticipant::class,'activity_id','id');
    }

    public function getCreateTimeAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(request()->cookie('timezone'))->format('Y.m.d H:i:s');
    }

    public function getUpdateTimeAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(request()->cookie('timezone'))->format('Y.m.d H:i:s');
    }
    
    public function getTimeBeginAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(request()->cookie('timezone'))->format('Y.m.d H:i:s');
    }

    public function getTimeEndAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(request()->cookie('timezone'))->format('Y.m.d H:i:s');
    }
    
    
    
}
