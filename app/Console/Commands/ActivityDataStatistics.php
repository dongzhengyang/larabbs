<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use OSS\OssClient;
use OSS\Core\OssException;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use App\Models\Mongo\Activity as mongoActivity;
use App\Models\Activity as ActivitySQL;
use App\Models\ActivityParticipant;
use App\Models\Mongo\Medal;
use App\Models\Mongo\UserMedal;
use App\Models\Mongo\User;
use MongoDB\BSON\ObjectId;


class ActivityDataStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabbs:activity-data-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '活动数据汇总';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$begintime = new UTCDateTime(1585670400*1000);
        //$endtime = new UTCDateTime(1602431999*1000);
        //5ea64757e86a9a305c060c02
        $list = ActivitySQL::all()->toArray();

        foreach ($list as $key => $activity){
            $updateData = [];
            $light_activity_num =  ActivityParticipant::where(['activity_id'=>$activity['id'],'light_activity'=>1])->count();
            $light_topic_num =  ActivityParticipant::where(['activity_id'=>$activity['id'],'light_topic'=>1])->count();
            $updateData['activity_count'] = $light_activity_num;
            $updateData['topic_count'] = $light_topic_num;
            ActivitySQL::where('id',$activity['id'])->update($updateData);
            echo  $activity['id']."\r\n";
        }



//        $begintime = 1585670400;
//        $endtime = 1602431999;
//        $activityList = mongoActivity::where(['time_beg' => ['$gte' => $begintime]])->get()->toArray();
//        foreach ($activityList as $item) {
//
//            if(strlen($item['time_beg'])>10){
//                continue;
//            }
//
//            $isexist = ActivitySQL::where('mongo_activity_id',(string)$item['_id'])->first();
//            if($isexist){
//                continue;
//            }
//
//            $data = [];
//            $participantCount = count($item['participants']);
//            $no = str_pad($item['no'], 11, "0", STR_PAD_LEFT);
//            $data = [
//                'activity_number' => $no,
//                'name' => (string)$item['name'],
//                'time_begin' => (new UTCDateTime($item['time_beg'] * 1000))->toDateTime(),
//                'time_end' => (new UTCDateTime($item['time_end'] * 1000))->toDateTime(),
//                'part_count' => $participantCount,
//                'mongo_activity_id' => (string)$item['_id'],
//                'status' => 1,
//                'create_time' => (new UTCDateTime(strtotime($item['created_at']) * 1000))->toDateTime(),
//                'update_time' => (new UTCDateTime(strtotime($item['updated_at']) * 1000))->toDateTime(),
//            ];
//            $activity = ActivitySQL::create($data);
//            if ($participantCount > 0 && $activity) {
//                //TODO 获取活动所对应的勋章
//                $medalIds = [];
//                $medals = Medal::where('act_id', new ObjectId($activity->mongo_activity_id))->get()->toArray();
//                if ($medals) {
//                    foreach ($medals as $k => $medal) {
//                        array_push($medalIds, $medal['id']);
//                    }
//                }
//
//                foreach ((array)$item['participants'] as $key => $value) {
//                    $light_activity = 0;
//                    $light_topic = 0;
//                    $user = User::where('user_id', $value['user_id'])->first();
//                    if ($medalIds && $user) {
//                        $lights = UserMedal::where("user_id", $user->id)->whereIn('medal_id',$medalIds)->get()->toArray();
//                        foreach($lights as $key1=>$value1){
//                            if (isset($value1['records']) && count($value1['records']) > 0) {
//                                $light_activity = 1;
//                            }
//                            if (isset($value1['light']) && $value1['light']) {
//                                $light_topic = 1;
//                            }
//
//                            if($light_activity && $light_topic){
//                                break;
//                            }
//                        }
//                     }
//                    $addData = [
//                        'activity_id' => $activity->id,
//                        'activity_number' => $activity->activity_number,
//                        'dline_user_id' => (int)$value['user_id'],
//                        'light_activity' => $light_activity,
//                        'light_topic' => $light_topic,
//    //                    'create_time' => isset($value['created_at']) ? (new UTCDateTime($value['created_at'] * 1000))->toDateTime() : null,
//    //                    'update_time' => isset($value['updated_at']) ? (new UTCDateTime($value['updated_at'] * 1000))->toDateTime() : null,
//                        'created_at' => (new UTCDateTime(time() * 1000))->toDateTime(),
//                        'updated_at' => (new UTCDateTime(time() * 1000))->toDateTime()
//                    ];
//                    ActivityParticipant::create($addData);
//                }
//            }
//
//            sleep(1);
//        }
    }
}




