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
            ['created_at' => ['$gt' => $datetime]]
        );
        $cursor = $manager->executeQuery('oplog.rs', $query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();

        while (true) {
            if ($iterator->valid()) {
                $document = ($iterator->current());
                dd($document);
            }
        }



//        $count =  UserBehavior::where('created_at','>=',$datetime)->count();
//        //$data = UserBehavior::first();
//        dd($count);

    }
}
