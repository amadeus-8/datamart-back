<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('region_d')->nullable()->after('referrer_id');
            $table->integer('age_id')->nullable()->after('referrer_id');
            $table->string('age_category_name', 15)->nullable()->after('referrer_id');
            $table->integer('status_id')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('ogpo_vts_count')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('medical_count')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('megapolis_count')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('amortization_count')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('kasko_count')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('kommesk_comfort_count')->default(0)->after('referrer_id');
            $table->unsignedTinyInteger('tour_count')->default(0)->after('referrer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('region_d');
            $table->dropColumn('age_id');
            $table->dropColumn('age_category_name');
        });
    }
}
