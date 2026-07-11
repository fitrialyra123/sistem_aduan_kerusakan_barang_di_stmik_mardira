<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            $table->string('method', 10)->nullable()->after('user_id');          // GET, POST, dll.
            $table->string('url', 2048)->nullable()->after('method');
            $table->ipAddress('ip_address')->nullable()->after('url');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('aksi', 100)->nullable()->after('user_agent');        // Deskripsi aksi, misal "login", "store_complaint"
            $table->integer('status_code')->nullable()->after('aksi');
            $table->string('exception_class')->nullable()->after('status_code');
            $table->text('exception_message')->nullable()->after('exception_class');
            $table->longText('exception_trace')->nullable()->after('exception_message');
            $table->boolean('is_error')->default(false)->after('exception_trace');
        });
    }

    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            // Kembalikan kolom lama jika rollback
            $table->dropColumn([
                'user_id', 'method', 'url', 'ip_address', 'user_agent',
                'aksi', 'status_code', 'exception_class', 'exception_message',
                'exception_trace', 'is_error'
            ]);

            $table->string('level', 50)->nullable();
            $table->text('message')->nullable();
            $table->string('action', 7)->nullable();
            $table->json('context')->nullable();
        });
    }
};