<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_document', function (Blueprint $table) {
            $table->string('document_id', 36)->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_document', function (Blueprint $table) {
            $table->char('document_id', 30)->change();
        });
    }
};
