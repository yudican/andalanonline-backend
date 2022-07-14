<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToScheduleShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->bigInteger('parent_id')->nullable()->after('schedule_type');
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
            $table->dropColumn('parent_id');
        });
    }
}
