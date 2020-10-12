<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mongo\Cycling;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use OSS\OssClient;
use OSS\Core\OssException;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;
use App\Models\Mongo\Activity as mongoActivity;
use App\Models\Activity;
use App\Models\ActivityParticipant;


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
        $begintime = 1585670400;
        $endtime = 1602431999;
        $host = sprintf("mongodb://%s:%s@%s:%s/admin",
            env('MONGO_DB_USERNAME'),
            rawurlencode(env('MONGO_DB_PASSWORD')),
            env('MONGO_DB_HOST', 'localhost'),
            env('MONGO_DB_PORT', '27017'));

        $manager = new Manager($host, ['socketTimeoutMS' => 900000]);
        $query = new Query(
            ['time_beg' => ['$gte' => $begintime],'time_beg'=>['$lt'=>$endtime]],
            ['noCursorTimeout'=>true,'sort' => ['$natural' => -1]]
        );
        $num = 0;
        $cursor = $manager->executeQuery('ridelife.activities', $query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();
        try {
                if ($iterator->valid()) {
                    $document = ($iterator->current());
                    echo date("Y-m-d H:i:s",$document->time_beg)."\r\n";
//                    $this->info((string)$document->_id."==".$num);
//                    if(!isset($document->device_info) || empty($document->device_info)){
//                        $iterator->next();
//                        continue;
//                    }
//                    if(!isset($document->device_info->app_version)){
//                        $iterator->next();
//                        continue;
//                    }
//                    if($this->str_compare((string)$document->device_info->app_version,'2.4.0')== '-1'){
//                        $iterator->next();
//                        continue;
//                    }
//                    $log_id = (string)$document->log_id;
//                    $mongo_record_id = (string)$document->_id;
//
//                    //TODO 判断是否已在mysql中
//                    $exist = CyclingRecords::where('mongo_record_id',$mongo_record_id)->first();
//                    if($exist){
//                        $iterator->next();
//                        continue;
//                    }
//
//                    $cyclingdata = Cycling::where('log_id',$log_id)->first();
//                    if(empty($cyclingdata)){
//                        $iterator->next();
//                        continue;
//                    }
//
//                    if(!isset($cyclingdata->app_calculated_data) || empty($cyclingdata->app_calculated_data)){
//                        $iterator->next();
//                        continue;
//                    }
//
//                    $cyclingId = $cyclingdata->_id;
//                    list($file_size,$file_url) = $this->aliOss($cyclingId);
//
//                    if(empty($file_url)){
//                        $iterator->next();
//                        continue;
//                    }
//
//
//
//                    $record = [];
//
//                    $record['mongo_record_id'] = $mongo_record_id;
//                    $record['start_time'] = (int)$cyclingdata->startTime;
//                    $record['finish_time'] = (int)$cyclingdata->finishTime;
//                    $record['total_second'] = (int)$cyclingdata->totalSecond;
//                    $record['total_distance'] = $cyclingdata->totalDistance;
//                    $record['total_calories'] = $cyclingdata->totalCalories;
//                    $record['max_speed'] = $cyclingdata->maxSpeed;
//                    $record['avg_speed'] = $cyclingdata->avgSpeed;
//                    $record['avg_moving_speed'] = $cyclingdata->avgMovingSpeed;
//                    $record['file_size'] = $file_size;
//                    $record['file_url'] = (string)$cyclingdata->_id;
//
//                    $record['app_version'] = (string)$document->device_info->app_version;
//                    $record['os_version'] = (string)$document->device_info->os_version;
//                    $record['phone_brand'] = isset($document->device_info->phone_brand)?$document->device_info->phone_brand:'';
//
//                    $record['sportDuration'] = (string)$cyclingdata->app_calculated_data['sportDuration'];
//                    $record['sportDistance'] = (string)$cyclingdata->app_calculated_data['sportDistance'];
//
//                    $record['autoPauseDuration'] = (string)$cyclingdata->app_calculated_data['autoPauseDuration'];
//                    $record['invalidDurationByDrift'] = (string)$cyclingdata->app_calculated_data['invalidDurationByDrift'];
//                    $record['invalidDistanceByDrift'] = (string)$cyclingdata->app_calculated_data['invalidDistanceByDrift'];
//                    $record['invalidAutoPauseDistanceByDrift'] = (string)$cyclingdata->app_calculated_data['invalidAutoPauseDistanceByDrift'];
//                    $record['invalidDurationByHighSpeed'] = (string)$cyclingdata->app_calculated_data['invalidDurationByHighSpeed'];
//                    $record['invalidDistanceByHighSpeed'] = (string)$cyclingdata->app_calculated_data['invalidDistanceByHighSpeed'];
//                    $record['manualPauseDistance'] = (string)$cyclingdata->app_calculated_data['manualPauseDistance'];
//                    $record['autoPauseDistance'] = (string)$cyclingdata->app_calculated_data['autoPauseDistance'];
//                    $record['sportType'] = (string)$cyclingdata->app_calculated_data['sportType'];
//
//
//                    CyclingRecords::create($record);

                }
                $iterator->next();

        }catch(\Exception $e){
            print $e->getMessage();
        }




//        $count =  UserBehavior::where('created_at','>=',$datetime)->count();
//        //$data = UserBehavior::first();
//        dd($count);

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
