<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shift')->cascadeOnDelete();
            $table->string('schedule_title');
            $table->time('schedule_time');
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
        Schema::dropIfExists('schedule_shifts');
    }
}
