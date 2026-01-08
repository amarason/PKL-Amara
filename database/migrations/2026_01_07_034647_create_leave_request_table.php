<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request', function (Blueprint $table) {
            
            // PRIMARY KEY
            $table->char('leave_id', 30)->primary();

            // RELASI
            $table->char('internship_id', 30);           // FK ke internship
            $table->char('approved_by', 30)->nullable(); // FK ke users.user_id (admin)
            $table->timestamp('approved_at')->nullable();

            // DATA IZIN
            $table->date('leave_date');
            $table->text('reason');
            $table->string('document_path', 255)->nullable();

            // STATUS
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                  ->default('menunggu');

            // TIMESTAMP
            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('internship_id')
                  ->references('internship_id')
                  ->on('internship')
                  ->onDelete('cascade');

            $table->foreign('approved_by')
                  ->references('login_id')   
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request');
    }
};
