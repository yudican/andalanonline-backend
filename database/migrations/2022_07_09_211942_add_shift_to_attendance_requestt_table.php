<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShiftToAttendanceRequesttTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_request', function (Blueprint $table) {
            $table->foreignId('shift_id')->constrained('shift')->cascadeOnDelete()->after('request_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_request', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });
    }
}
