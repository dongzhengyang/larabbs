<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Activity;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class ActivityParticipant extends Model
{
    protected $table = 'activity_participants';

    protected $dates = ['create_time','update_time'];

    protected $fillable = [
        'activity_id','activity_number','dline_user_id','light_activity','light_topic',
        'create_time','update_time'
    ];

    public function activityInfo(): BelongsTo
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

    public function updateActivityParticipant($participants, $activityId, $activityNumber)
    {
        //TODO 查询活动
        $newUserIds = array_column($participants, 'user_id');
        $oldUserIds = ActivityParticipant::where('activity_id', $activityId)->pluck('dline_user_id')->toArray();
        //TODO 需要写入的数据
        $newDiffResult = array_diff($newUserIds, $oldUserIds);
        //TODO 需要删除的数据
        $oldDiffResult = array_diff($oldUserIds, $newUserIds);
        //TODO
        if (count($newDiffResult) > 0) {
            $addData = [];
            $num = 0;
            foreach ((array) $participants as $key => $value) {
                if (in_array($value['user_id'], $newDiffResult)) {
                    $num ++;
                    $addData[] = [
                        'activity_id' => $activityId,
                        'activity_number' =>  $activityNumber,
                        'dline_user_id' => (int) $value['user_id'],
                        'create_time' => isset($value['created_at']) ? (new UTCDateTime($value['created_at'] * 1000))->toDateTime() : null,
                        'update_time' => isset($value['updated_at']) ? (new UTCDateTime($value['updated_at'] * 1000))->toDateTime() : null,
                        'created_at' => (new UTCDateTime(time() * 1000))->toDateTime(),
                        'updated_at' => (new UTCDateTime(time() * 1000))->toDateTime()
                    ];
                }

                if ($num % 200 == 0) {
                    ActivityParticipant::insert($addData);
                    unset($addData);
                    $addData = [];
                    $num = 0;
                }
            }
            if ($addData) {
                ActivityParticipant::insert($addData);
            }
        }
        if (count($oldDiffResult) > 0) {
            ActivityParticipant::where('activity_id', $activityId)->whereIn('dline_user_id', $oldDiffResult)->delete();
        }

        return true;
    }
}
