<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityParticipant extends Model
{
    use SoftDeletes;

    protected $table = 'activity_participants';
    
    protected $dates = ['create_time','update_time'];
    
    protected $fillable = [
        'activity_id','activity_number','dline_user_id','light_activity','light_topic',
        'create_time','update_time'
    ];
    
    public function activityInfo() : BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
    
    public function getCreateTimeAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(request()->cookie('timezone'))->format('Y.m.d H:i:s');
    }

    public function getUpdateTimeAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(request()->cookie('timezone'))->format('Y.m.d H:i:s');
    }
}
