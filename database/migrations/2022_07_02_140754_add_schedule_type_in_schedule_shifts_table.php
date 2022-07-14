<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleTypeInScheduleShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->enum('schedule_type', ['checkin', 'checkout', 'breakin', 'breakout'])->default('checkin')->after('schedule_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->dropColumn('schedule_type');
        });
    }
}
