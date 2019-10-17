<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKBMReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('k_b_m_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('report_id');
            $table->date('date');
            $table->string('isurance_class');
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
        Schema::dropIfExists('k_b_m_reports');
    }
}
