<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\UserData;

class UpdateMileageSort implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userData)
    {
        $this->userData = $userData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $oldMil = $this->userData['mileage']-0.3;
        $newMil = $this->userData['mileage'];
        $this->cal_rank($oldMil,$newMil,$this->userData['user_id']);
    }
    
    private function cal_rank($oldMil,$newMil,$user_id){
        if($newMil>$oldMil){
            $userdata = new UserData;
            $userdata->change_user_rank($oldMil,$newMil,$user_id);
        }
    }
    
    
    
}
