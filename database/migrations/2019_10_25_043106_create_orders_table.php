<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('ogpo_vts_result', 10, 2)->default(0);
            $table->double('vts_cross_result', 10, 2)->default(0);
            $table->double('vts_overall_sum', 10, 2);
            $table->double('avg_sum', 10, 2);
            $table->double('avg_cross_result', 8, 2)->default(0);
            $table->unsignedTinyInteger('overall_lost_count')->default(0);
            $table->unsignedTinyInteger('vts_lost_count')->default(0);
            $table->unsignedTinyInteger('declared_claims')->default(0);
            $table->unsignedTinyInteger('pending_claims')->default(0);
            $table->unsignedTinyInteger('accepted_claims')->default(0);
            $table->unsignedTinyInteger('payout_reject_claims')->default(0);
            $table->unsignedTinyInteger('client_reject_claims')->default(0);
            $table->double('payout_sum', 10, 2)->default(0);
            $table->string('isn', 15);
            //  $table->string('client_isn', 15);

            // relations
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('time_id');
            $table->unsignedInteger('gift_id')->nullable();
            $table->unsignedTinyInteger('department_id')->nullable();
            $table->unsignedTinyInteger('sale_channel_id')->nullable();
            $table->unsignedInteger('agent_id')->nullable();
            $table->unsignedTinyInteger('sale_center_id')->nullable();
            $table->unsignedTinyInteger('referrer_id')->nullable();
            // products many to many

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
        Schema::dropIfExists('orders');
    }
}
