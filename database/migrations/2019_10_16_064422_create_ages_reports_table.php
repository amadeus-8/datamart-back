<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgesReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ages_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('report_id');
            $table->date('date');
            $table->string('age');
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
        Schema::dropIfExists('ages_reports');
    }
}
