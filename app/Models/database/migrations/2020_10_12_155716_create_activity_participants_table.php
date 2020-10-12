<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('activity_id')->unsigned()->comment('mysql活动编号');
            $table->foreign('activity_id')->references('id')->on('activity');
            $table->integer('activity_number')->unsigned()->comment('鼎连的活动编号');
            $table->integer('dline_user_id')->unsigned()->comment('鼎连用户ID');
            $table->integer('light_activity')->unsigned()->default(0)->comment('是否点亮活动勋章');
            $table->integer('light_topic')->unsigned()->default(0)->comment('是否点亮主题勋章');
            $table->timestamp('create_time')->nullable()->comment('mongo 创建时间');
            $table->timestamp('update_time')->nullable()->comment('mongo 更新时间');
            $table->timestamps();
            $table->index(['activity_id', 'dline_user_id'],'index_activity_dline_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_participants');
    }
}
