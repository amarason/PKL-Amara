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
        Schema::create('internship', function (Blueprint $table) {

            // Primary Key
            $table->char('internship_id', 30)->primary();

            // Periode PKL
            $table->date('start_date');
            $table->date('end_date');

            // Status PKL
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');

            // Foreign Keys
            $table->unsignedBigInteger('user_id');
            $table->char('institution_id', 30);
            $table->char('major_id', 30);

            // Timestamps (Otomatis menciptakan created_at dan updated_at)
            $table->timestamps();

            // =========================
            // Relasi ke tabel users
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Relasi ke tabel institution
            $table->foreign('institution_id')
                  ->references('institution_id')
                  ->on('institution')
                  ->onDelete('restrict');

            // Relasi ke tabel major
            $table->foreign('major_id')
                  ->references('major_id')
                  ->on('major')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship');
    }
};