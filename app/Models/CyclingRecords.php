<?php


namespace App\Models;


class CyclingRecords extends model
{
    protected $fillable = ['mongo_record_id', 'start_time', 'finish_time',
                            'total_second', 'total_distance', 'total_calories',
                            'max_speed','avg_speed','avg_moving_speed','file_size',
                            'file_url'
                          ];
}
