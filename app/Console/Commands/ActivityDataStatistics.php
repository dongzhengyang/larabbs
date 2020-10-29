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
use App\Models\Mongo\Session;
use MongoDB\BSON\ObjectId;
use App\Models\Mongo\Cycling;
use App\Models\KsUser;
use App\Models\UserData;


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

        $userIds = [2617475,
            2473742,
            2055458,
            2380818,
            2602720,
            2602900,
            2603900,
            2175326,
            2602898,
            2603846,
            2602759,
            2602930,
            1387712,
            2603838,
            2164295,
            2602641,
            2194906,
            2602754,
            3281325,
            2314275,
            2602652,
            2658148,
            3277265,
            2355196,
            2830805,
            2610828,
            787390,
            2967147,
            3024648,
            2965788,
            3012097,
            2679250,
            2602931,
            2737898,
            2966200,
            2824331,
            3013309,
            2815707,
            2156525,
            2175323,
            2476439,
            2944302,
            2164305,
            3177936,
            2602340,
            3277771,
            848581,
            823242,
            2175414,
            2063587,
            2620511,
            2602400,
            2063640,
            2062220,
            2063599,
            2062072,
            2063585,
            2673924,
            1658268,
            2175800,
            1357196,
            1729407,
            1428828,
            1966282,
            2602404,
            2156988,
            2991915,
            2061982,
            2569715,
            2469607,
            2652593,
            2562168,
            2829539,
            2585002,
            2674045,
            2829575,
            2157185,
            2063613,
            3065305,
            2055707,
            2166984,
            2950068,
            2067432,
            550146,
            2589540,
            2065924,
            2602895,
            2067429,
            2062204,
            2824902,
            2155907,
            2643632,
            1817445,
            2914679,
            2601229,
            2157078,
            3153567,
            2063846,
            2393450,
            2155873,
            2062034,
            2556281,
            1656023,
            2157694,
            2474121,
            1550207,
            2179853,
            1357241,
            1381518,
            2595267,
            1509479,
            2354943,
            2370851,
            2555200,
            3119046,
            2549580,
            2463659,
            2441953,
            3279032,
            3245037,
            3291420,
            2668333,
            1357264,
            3214273,
            2046379,
            2277310,
            2585886,
            3237491,
            2846429,
            2596716,
            1405533,
            2279664,
            2588841,
            2585921,
            2064099,
            2065935,
            2907828,
            2650275,
            1981369,
            1720618,
            2064271,
            3251668,
            3144565,
            2996131,
            2089146,
            2927223,
            2065946,
            1521205,
            2067435,
            2954625,
            2066852,
            2942048,
            3161380,
            2991067,
            1483377,
            2925593,
            2925415,
            2990825,
            1936171,
            2856520,
            2065944,
            2603789,
            2974588,
            2958292,
            3144735,
            1722813,
            2602903,
            2924855,
            2829789,
            797323,
            3144717,
            2602892,
            2602911,
            2602745,
            2603785,
            2603794,
            2603773,
            2602886,
            2602908,
            2603779,
            2602906,
            2603788,
            2602725,
            2891042,
            2602917,
            2602653,
            2929117,
            2900508,
            2750261,
            1943923,
            2925448,
            1656152,
            2580727,
            2589542,
            2602676,
            2602717,
            2594149,
            1520708,
            2602657,
            3073565,
            2602722,
            3081959,
            1356978,
            2602735,
            2602737,
            3211967,
            2602671,
            2409356,
            1656296,
            3171046,
            3156622,
            2685418,
            2829646,
            3039646,
            1596671,
            2592269,
            2865072,
            2611419,
            2649992,
            2867374,
            2878269,
            2819744,
            3029862,
            2831742,
            2657708,
            2381032,
            2867304,
            2853632,
            2611507,
            1519969,
            2878347,
            797270,
            3180247,
            2864672,
            3062953,
            1986825,
            2062255,
            2614991,
            2039365,
            3091830,
            1563474,
            2614999,
            2587863,
            2062454,
            2891457,
            2816811,
            3292963,
            1108693,
            2064743,
            2442131,
            2711403,
            2612011,
            2611398,
            2864669,
            3048724,
            2883097,
            2612006,
            2910409,
            2612280,
            569898,
            797101,
            2611392,
            2016036,
            2611713,
            2612019,
            2063625,
            2614912,
            1405550,
            3020902,
            2585922,
            3069157,
            3156615,
            2877385,
            797431,
            2863569,
            3213557,
            1382169,
            2610918,
            2003254,
            2249652,
            2611703,
            843295,
            2611707,
            3256750,
            2879908,
            2910171,
            816174,
            3264746,
            2869923,
            2875312,
            2611511,
            2434094,
            2186300,
            2267163,
            2612953,
            822600,
            3100616,
            2921802,
            1827910,
            2649484,
            2189172,
            2346037,
            3052749,
            3155867,
            2849085,
            2590667,
            2917441,
            3275565,
            3304540,
            2883165,
            2631279,
            2889168,
            2597486,
            3313843,
            2589209,
            3250939,
            2421184,
            2941447,
            2597725,
            3212794,
            1706433,
            2037983,
            3180269,
            2588608,
            2889834,
            3293410,
            1796094,
            2588842,
            3142414,
            3141721,
            2239087,
            2869999,
            2875273,
            2873472,
            3142986,
            2079117,
            2484622,
            2185145,
            3068262,
            792501,
            2046332,
            3222357,
            2089732,
            3141811,
            2746939,
            3144539,
            3144659,
            1704707,
            3153399,
            2882392,
            3159603,
            3158045,
            3278947,
            3320496,
            3259880,
            2017444,
            2274595,
            3224257,
            844842,
            3153369,
            1723946,
            3088182,
            1092364,
            3269795,
            2901799,
            3109330,
            3075981,
            1723053,
            974242,
            3176976,
            2619712,
            2665908,
            2722486,
            3039469,
            3277666,
            3165934,
            2534547,
            3308905,
            3308968,
            3319361,
            1623241,
            1540632,
            3202624,
            3313891,
        ];

        foreach ($userIds as $userId){
           $userinfo =  User::where('user_id',(int)$userId)->first(['_id']);
           $session = Session::where('user_id',(string)$userinfo->_id)->sortBy('_id',-1)->first();
           UserData::create(['user_id'=>$userId,'mongo_user_id'=>$session->model]);
           dd($session);
        }

        exit();

        $begintime = 1598889600;//2020/09/01
        $userList = KsUser::where("flag",0)->get()->toArray();
        foreach ($userList as $key=>$user){
            $update = [];
            $mongoUser = User::where('user_id',$user['user_id'])->first();
            if(empty($mongoUser)){
                $update['flag'] = 2;
                $update['total_mil'] = 0;
                KsUser::where('id',$user['id'])->update($update);
                continue;
            }

            $cyclingRecords = Cycling::where('user_id',(string)$mongoUser->_id)->where(['startTime'=>['$gte' => $begintime]])->get()->toArray();
            if($cyclingRecords){
                $total = 0;
                foreach ($cyclingRecords as $k=>$v){
                    $total = $total+(float)$v['totalDistance'];
                }
                $update['flag'] = 1;
                $update['total_mil'] = $total;
                KsUser::where('id',$user['id'])->update($update);
                continue;

            }else{
                $update['flag'] = 1;
                $update['total_mil'] = 0;
                KsUser::where('id',$user['id'])->update($update);
                continue;
            }

        }


        //$begintime = new UTCDateTime(1585670400*1000);
        //$endtime = new UTCDateTime(1602431999*1000);
        //5ea64757e86a9a305c060c02
//        $list = ActivitySQL::all()->toArray();
//
//        foreach ($list as $key => $activity){
//            $updateData = [];
//            $light_activity_num =  ActivityParticipant::where(['activity_id'=>$activity['id'],'light_activity'=>1])->count();
//            $light_topic_num =  ActivityParticipant::where(['activity_id'=>$activity['id'],'light_topic'=>1])->count();
//            $updateData['activity_count'] = $light_activity_num;
//            $updateData['topic_count'] = $light_topic_num;
//            ActivitySQL::where('id',$activity['id'])->update($updateData);
//            echo  $activity['id']."\r\n";
//        }



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




