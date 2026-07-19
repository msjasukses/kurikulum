<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tautan ke tabel "guru" di database datacenter (tanpa FK lintas DB),
            // dipakai akun guru hasil login NIP datacenter.
            $table->unsignedBigInteger('guru_id')->nullable()->after('pegawai_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('guru_id');
        });
    }
};
