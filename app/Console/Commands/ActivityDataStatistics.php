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
            ['time_beg' => ['$gte' => $begintime]],
            ['sort' => ['_id' => 1]]
        );
        $num = 0;
        $cursor = $manager->executeQuery('ridelife.activities', $query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();
        try {
            if ($iterator->valid()) {
                $document = ($iterator->current());
                if(strlen($document->time_beg)>10){
                    $time_beg = substr($document->time_beg , 0 , 10);
                    print_r($time_beg.'=='.date("Y-m-d H:i:s", $time_beg) . "\r\n") ;
                }else{
                    print_r( $document->time_beg.'=='.date("Y-m-d H:i:s", $document->time_beg) . "\r\n");
                }


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
