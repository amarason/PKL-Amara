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
        Schema::table('users', function (Blueprint $table) {

            // ID login PKL (misal: PKL26/S1/001)
            $table->char('login_id', 30)->unique()->after('id');

            // Role user (ADMIN / PKL)
            $table->char('role_id', 30)->after('login_id');

            // Status aktif user
            $table->boolean('is_active')->default(true)->after('password');

            // Hapus email (tidak dipakai)
            $table->dropColumn(['email', 'email_verified_at']);
        });

        // Tambahkan foreign key setelah kolom ada
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('role_id')
                  ->on('role')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Kembalikan email
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();

            // Hapus kolom tambahan
            $table->dropForeign(['role_id']);
            $table->dropColumn(['login_id', 'role_id', 'is_active']);
        });
    }
};
