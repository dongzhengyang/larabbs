<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('mileage',8,2)->default(0.00)->comment('里程数')->index('mileage');
            $table->string('mongo_user_id',100)->nullable()->comment('mongo用户ID')->index('mongo_user_id');
            $table->bigInteger('user_id')->nullable()->comment('mysql用户ID')->index('user_id');
            $table->bigInteger('sort')->default(0)->comment('里程排名')->index('sort');
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
        Schema::dropIfExists('user_data');
    }
}
