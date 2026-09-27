<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kunci API AI milik masing-masing akun (admin & guru) untuk
            // fitur Generate Modul Ajar. Kunci disimpan terenkripsi lewat
            // cast "encrypted" di App\Models\User.
            $table->string('ai_provider')->nullable()->after('aktif');
            $table->text('ai_api_key')->nullable()->after('ai_provider');
            $table->string('ai_model')->nullable()->after('ai_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_api_key', 'ai_model']);
        });
    }
};
