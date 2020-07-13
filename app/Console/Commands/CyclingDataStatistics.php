<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mongo\UserBehavior;
use App\Models\Mongo\Cycling;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use OSS\OssClient;
use OSS\Core\OssException;
use MongoDB\BSON\UTCDateTime;
use App\Models\CyclingRecords;


class CyclingDataStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabbs:cycling-data-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '骑行数据汇总';

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
        $datetime = (new UTCDateTime(1585670400*1000))->toDateTime();

        $host = sprintf("mongodb://%s:%s@%s:%s/admin",
            env('MONGO_DB_USERNAME'),
            rawurlencode(env('MONGO_DB_PASSWORD')),
            env('MONGO_DB_HOST', 'localhost'),
            env('MONGO_DB_PORT', '27017'));

        $manager = new Manager($host, ['socketTimeoutMS' => 900000]);
        $query = new Query(
            ['log_id' => ['$exists' => true]]
        );
        $cursor = $manager->executeQuery('ridelife.user_behavior', $query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();
        try {
            while (true) {
                if ($iterator->valid()) {
                    $document = ($iterator->current());;
                    if($this->str_compare((string)$document->device_info->app_version,'2.4.0')== '-1'){
                        $iterator->next();
                        continue;
                    }
                    $log_id = (string)$document->log_id;
                    $mongo_record_id = (string)$document->_id;

                    //TODO 判断是否已在mysql中
                    $exist = CyclingRecords::where('mongo_record_id',$mongo_record_id).first();
                    if($exist){
                        $iterator->next();
                        continue;
                    }

                    $cyclingdata = Cycling::where('log_id',$log_id)->first();
                    if(empty($cyclingdata)){
                        $iterator->next();
                        continue;
                    }

                    $cyclingId = $cyclingdata->_id;
                    list($file_size,$file_url) = $this->aliOss($cyclingId);

                    $record = [];

                    $record['mongo_record_id'] = $mongo_record_id;
                    $record['start_time'] = (int)$cyclingdata->startTime;
                    $record['finish_time'] = (int)$cyclingdata->finishTime;
                    $record['total_second'] = (int)$cyclingdata->totalSecond;
                    $record['total_distance'] = $cyclingdata->totalDistance;
                    $record['total_calories'] = $cyclingdata->totalCalories;
                    $record['max_speed'] = $cyclingdata->maxSpeed;
                    $record['avg_speed'] = $cyclingdata->avgSpeed;
                    $record['avg_moving_speed'] = $cyclingdata->avgMovingSpeed;
                    $record['file_size'] = $file_size;
                    $record['file_url'] =$file_url;

                    CyclingRecords::create($record);

                }
                $iterator->next();
            }

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

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $object = "cyclingData/".$cyclingId;
            $objectMeta = $ossClient->getObjectMeta($this->bucket, $object);
            $size = $objectMeta['content-length'];
            $url = $objectMeta['info']['url'];
            //dd($objectMeta,$objectMeta['content-length']/1024,$objectMeta['info']['url']);
        } catch (OssException $e) {
            print $e->getMessage();
        }

        return [$size,$url];
    }
}
