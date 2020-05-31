<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserData;

class CalculateMileageSort extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabbs:calculate-mileage-sort';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算用户里程排名';

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
    public function handle(UserData $userData)
    {
        //$userData->cal_users_rank();
        //$userData->change_user_rank();
        $userData->get_rank_100();
    }
}
