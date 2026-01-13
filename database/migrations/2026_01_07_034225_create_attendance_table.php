<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {

            // Primary Key
            $table->char('attendance_id', 30)->primary();

            // Relasi PKL
            $table->char('internship_id', 30);

            // Tanggal & waktu
            $table->date('attendance_date');
            $table->time('check_in_time');
            $table->time('check_out_time')->nullable();

            // Bukti foto
            $table->string('check_in_photo', 255);
            $table->string('check_out_photo', 255)->nullable();

            // Status kehadiran
            $table->enum('status', ['hadir', 'alpha', 'izin'])->default('hadir');

            // Koreksi oleh admin
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('update_reason')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('internship_id')
                  ->references('internship_id')
                  ->on('internship')
                  ->onDelete('cascade');

            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // 1 peserta hanya 1 absensi per hari
            $table->unique(['internship_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
