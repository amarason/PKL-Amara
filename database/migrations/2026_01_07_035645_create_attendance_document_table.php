<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_document', function (Blueprint $table) {

            // Primary Key
            $table->char('document_id', 30)->primary();

            // Foreign Keys
            $table->char('internship_id', 30);              // relasi ke internship
            $table->unsignedBigInteger('generated_by');     // user (admin / PKL)

            // Periode Laporan
            $table->date('period_start');
            $table->date('period_end');

            // File & QR
            $table->string('file_path', 255);
            $table->string('qr_hash', 255);

            $table->timestamp('generated_at');

            // Constraints
            $table->foreign('internship_id')
                  ->references('internship_id')
                  ->on('internship')
                  ->onDelete('cascade');

            $table->foreign('generated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_document');
    }
};
