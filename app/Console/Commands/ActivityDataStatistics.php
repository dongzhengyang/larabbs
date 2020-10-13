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

        $begintime = 1585670400;
        $endtime = 1602431999;
        $activityList = mongoActivity::where(['time_beg'=>['$gte' => $begintime],'time_beg'=>['$lt'=>$endtime],'id'=>'5ea64757e86a9a305c060c02'])->get()->toArray();
        foreach ($activityList as $item) {
            $data = [];
            $participantCount = count($item['participants']);
            $no = str_pad($item['no'],11,"0",STR_PAD_LEFT);
            $data = [
                'activity_number' => $no,
                'name' => (string)$item['name'],
                'time_begin' => (new UTCDateTime($item['time_beg']*1000))->toDateTime(),
                'time_end' => (new UTCDateTime($item['time_end']*1000))->toDateTime(),
                'part_count' => $participantCount,
                'mongo_activity_id' =>(string)$item['_id'],
                'status' => 1,
                'create_time' => (new UTCDateTime($item['created_at']))->toDateTime(),
                'update_time' => (new UTCDateTime($item['updated_at']))->toDateTime(),
            ];

            $medals = Medal::where('act_id',new ObjectId($item['_id']))->get()->toArray();
            if($medals){
                foreach ($medals as $k=>$medal){
                    array_push($medalIds,$medal['id']);
                }
            }
dd($medals,$medalIds);

            $activity = ActivitySQL::create($data);
            if($participantCount>0 && $activity){

                //TODO 获取活动所对应的勋章
                $light_activity = 0;
                $light_topic = 0;
                $medalIds = [];
                $medals = Medal::where('act_id',new ObjectId($activity->mongo_activity_id))->get()->toArray();
                if($medals){
                    foreach ($medals as $k=>$medal){
                        array_push($medalIds,$medal['id']);
                    }
                }



                foreach ((array) $item['participants'] as $key => $value) {

                    $addData = [
                        'activity_id' => $activity->id,
                        'activity_number' =>  $activity->activity_number,
                        'dline_user_id' => (int) $value['user_id'],
                        'light_activity' => $light_activity,
                        'light_topic' => $light_topic,
                        'create_time' => isset($value['created_at']) ? (new UTCDateTime($value['created_at'] * 1000))->toDateTime() : null,
                        'update_time' => isset($value['updated_at']) ? (new UTCDateTime($value['updated_at'] * 1000))->toDateTime() : null,
                        'created_at' => (new UTCDateTime(time() * 1000))->toDateTime(),
                        'updated_at' => (new UTCDateTime(time() * 1000))->toDateTime()
                    ];
                    ActivityParticipant::create($addData)
                }
            }




        }








        $host = sprintf("mongodb://%s:%s@%s:%s/admin",
            env('MONGO_DB_USERNAME'),
            rawurlencode(env('MONGO_DB_PASSWORD')),
            env('MONGO_DB_HOST', 'localhost'),
            env('MONGO_DB_PORT', '27017'));

        $manager = new Manager($host, ['socketTimeoutMS' => 900000]);
        $query = new Query(
            ['time_beg' => ['$gte' => $begintime],'time_beg'=>['$lt'=>$endtime],'id'=>"5e49fbbee86a9a28ac404f7c"],
            ['sort' => ['_id' => 1]]
        );
        $num = 0;
        $cursor = $manager->executeQuery('ridelife.activities', $query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();
        try {
            while ($num<=5911) {
                if ($iterator->valid()) {
                    $document = ($iterator->current());
                    if(strlen($document->time_beg)>10){
                        $iterator->next();
                        continue;
                    }

                    $data = [];
                    $participantCount = count($document->participants);
                    $no = str_pad($document->no,11,"0",STR_PAD_LEFT);
                    $data = [
                        'activity_number' => $no,
                        'name' => (string)$document->name,
                        'time_begin' => $document->time_beg,
                        'time_end' => $document->time_end,
                        'part_count' => $participantCount,
                        'mongo_activity_id' =>(string)$document->_id,
                        'status' => 1,
                        'create_time' => (new UTCDateTime((string)$document->created_at))->toDateTime(),
                        'update_time' => (new UTCDateTime((string)$document->updated_at))->toDateTime(),
                    ];
//                    print_r($data);
//                    print_r($document->participants);
                    $activity = ActivitySQL::create($data);




                    //echo $document->time_beg.'=='.date("Y-m-d H:i:s", $document->time_beg) . "\r\n";
                    $num ++;
                }
                $iterator->next();
            }
        }catch(\Exception $e){
            print $e->getMessage();
        }

        echo $num."===";




    }


    private function str_compare($str1, $str2)
    {
        $arr1 = explode('.', $str1);
        $arr2 = explode('.', $str2);
        for ($i = 0; $i < count($arr1); $i++) {
            if ($arr1[$i] > $arr2[$i]) {
                return 1;
            } elseif ($arr1[$i] < $arr2[$i]) {
                return -1;
            }
        }
        return 0;
    }

    private function aliOss($cyclingId){
        $accessKeyId  =  env('ALIYUN_ACCESS_KEY_ID');
        $accessKeySecret  = env('ALIYUN_ACCESS_KEY_SECRET');
        $endpoint  = env('ALIYUN_END_POINT');
        $bucket = env('ALIYUN_BUCKET');
        $size = 0;
        $url = '';
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $object = "cyclingData/".$cyclingId;
            $objectMeta = $ossClient->getObjectMeta($bucket, $object);
            $size = $objectMeta['content-length'];
            $url = $objectMeta['info']['url'];
            //dd($objectMeta,$objectMeta['content-length']/1024,$objectMeta['info']['url']);
        } catch (OssException $e) {
            //print $e->getMessage();
        }

        return [$size,$url];
    }
}
