<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_masuk_kerja')->nullable();
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan'])->nullable();
            $table->string('alamat')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->string('foto_wajah')->nullable();
            $table->foreignId('cabang_id')->nullable()->constrained('cabang')->cascadeOnDelete();
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->cascadeOnDelete();

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
        Schema::dropIfExists('user_profiles');
    }
}
