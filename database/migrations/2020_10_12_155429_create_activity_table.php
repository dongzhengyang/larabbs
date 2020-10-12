<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('activity_number')->unsigned()->index()->comment('活动编号');
            $table->string('name', 100)->comment('活动名称');
            $table->timestamp('time_begin')->nullable()->index()->comment('活动开始时间');
            $table->timestamp('time_end')->nullable()->comment('活动结束时间');
            $table->integer('part_count')->unsigned()->default(0)->comment('报名人数');
            $table->integer('activity_count')->unsigned()->default(0)->comment('点亮活动勋章人数');
            $table->integer('topic_count')->unsigned()->default(0)->comment('点亮主题勋章人数');
            $table->string('mongo_activity_id', 32)->comment('mongo 活动id');
            $table->unique('mongo_activity_id');
            $table->timestamp('create_time')->nullable()->comment('mongo 创建时间');
            $table->timestamp('update_time')->nullable()->comment('mongo 更新时间');
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
        Schema::dropIfExists('activity');
    }
}
