<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_shift_id')->constrained('schedule_shifts')->cascadeOnDelete();
            $table->dateTime('attendance_date')->nullable();
            $table->char('attendance_status', 1)->default(1); // 0 = absent, 1 = present
            $table->char('attendance_request_status', 1)->default(1); // 0 = reject, 1 = acc
            $table->string('attendance_photo')->nullable();
            $table->string('attendance_note')->nullable();
            $table->boolean('attendance_active')->default(false);
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
        Schema::dropIfExists('attendances');
    }
}
