<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mongo\UserBehavior;
use App\Models\Mongo\Cycling;
use OSS\OssClient;
use OSS\Core\OssException;


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
        $count =  UserBehavior::where('created_at','>','1585670400')->count();
        $data = UserBehavior::where('created_at','>','1585670400')->order('created_at',-1)->first();
        dd($count,$data);

    }
}
