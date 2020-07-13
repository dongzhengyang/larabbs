<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCyclingRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cycling_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mongo_record_id',32)->default('')->comment('user_behavior._id')->index('mongo_record_id');
            $table->unsignedInteger('start_time')->default(0)->comment('开始时间');
            $table->unsignedInteger('finish_time')->default(0)->comment('结束时间');
            $table->unsignedInteger('total_second')->default(0)->comment('总时间s');
            $table->string('total_distance',20)->default(0.00)->comment('总里程数');
            $table->string('total_calories',20)->default(0.00)->comment('总卡路里');
            $table->string('max_speed',20)->default(0.00)->comment('最大速度');
            $table->string('avg_speed',20)->default(0.00)->comment('平均速度');
            $table->string('avg_moving_speed',20)->default(0.00)->comment('平均移动速度');
            $table->string('file_size',20)->default(0.00)->comment('flatbuffer文件大小 byte');
            $table->string('file_url',150)->default('')->comment('文件地址');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cycling_records');
    }
}
