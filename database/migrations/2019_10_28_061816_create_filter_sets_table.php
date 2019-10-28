<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_sets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('period_category', 20);
//            $table->date('from');
//            $table->date('to');
            $table->unsignedTinyInteger('user_id');
            $table->unsignedTinyInteger('region_id');
            $table->unsignedTinyInteger('sale_center_id')->nullable();
            $table->char('gender', 1)->nullable();
            $table->unsignedTinyInteger('sale_channel_id')->nullable();
            $table->unsignedTinyInteger('agent_id')->nullable();
            $table->unsignedTinyInteger('referrer_id')->nullable();
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
        Schema::dropIfExists('filter_sets');
    }
}
