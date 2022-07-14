<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_request', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('request_type', ['sakit', 'izin', 'lembur', 'cuti']);
            $table->string('request_photo')->nullable();
            $table->text('request_description')->nullable();
            $table->dateTime('request_date')->nullable();
            $table->dateTime('request_end_date')->nullable();
            $table->char('request_status', 1)->nullable()->default(0); //0:waiting verification,1:acc,2:reject
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
        Schema::dropIfExists('attendance_request');
    }
}
