<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->string('city');
            $table->string('sale_center')->nullable();
            $table->char('gender', 1)->nullable();
            $table->unsignedTinyInteger('age');
            $table->string('department');
            $table->string('age_category', '10');
            $table->string('insurance_class', 10)->nullable();
            $table->string('bonus', 20)->nullable();
            $table->string('gift')->nullable();
            $table->string('sale_channel', 40);
            $table->string('agent', 100)->nullable();
            $table->string('source', 40);
            $table->boolean('new')->default(false); //
            $table->boolean('active')->default(false);
            $table->boolean('returned')->default(false);
            $table->boolean('cabinet')->default(false);
            $table->boolean('telemarketing')->default(false);
            $table->string('referrer')->nullable();
            $table->unsignedTinyInteger('ogpo_vts_count');
            $table->unsignedTinyInteger('medical_count');
            $table->unsignedTinyInteger('megapolis_count');
            $table->unsignedTinyInteger('amortization_count');
            $table->unsignedTinyInteger('kasko_count');
            $table->unsignedTinyInteger('kommesk_comfort_count');
            $table->unsignedTinyInteger('tour_count');
            $table->double('ogpo_vts_result', 10, 2);
            $table->double('vts_cross_result', 10, 2)->default(0);
            $table->double('vts_overall_sum', 10, 2);
            $table->double('avg_sum', 10, 2);
            $table->double('avg_cross_result', 8, 2)->default(0);
            $table->unsignedTinyInteger('overall_lost_count')->default(0);
            $table->unsignedTinyInteger('vts_lost_count')->default(0);
            $table->unsignedTinyInteger('declared_claims')->default(false);
            $table->unsignedTinyInteger('pending_claims')->default(false);
            $table->unsignedTinyInteger('accepted_claims')->default(false);
            $table->unsignedTinyInteger('payout_reject_claims')->default(false);
            $table->unsignedTinyInteger('client_reject_claims')->default(false);
            $table->double('payout_sum', 10, 2)->default(0);
            $table->string('isn', 15);
            $table->string('client_isn', 15);
            $table->string('vehicle_brand', 40);
            $table->string('vehicle_model', 50);
            $table->string('vehicle_year', 4);
            $table->string('vehicle_year_category', 25);
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
        Schema::dropIfExists('data');
    }
}
