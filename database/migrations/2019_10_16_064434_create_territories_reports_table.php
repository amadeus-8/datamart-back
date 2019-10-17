<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerritoriesReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('territories_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('report_id');
            $table->date('date');
            $table->string('territory');
            $table->integer('sale_count');
            $table->integer('payout_count');
            $table->bigInteger('sum');
            $table->bigInteger('lost_sum');
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
        Schema::dropIfExists('territories_reports');
    }
}
