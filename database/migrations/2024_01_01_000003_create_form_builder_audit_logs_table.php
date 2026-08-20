<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('form-builder.tables.audit_logs', 'form_builder_audit_logs');
        $forms = config('form-builder.tables.forms', 'form_builder_forms');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) use ($forms): void {
            $blueprint->id();
            $blueprint->foreignId('form_id')->nullable()->constrained($forms)->nullOnDelete();
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->string('action');
            $blueprint->json('payload')->nullable();
            $blueprint->string('ip_address', 45)->nullable();
            $blueprint->timestamps();

            $blueprint->index('action');
            $blueprint->index(['form_id', 'action']);
            $blueprint->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('form-builder.tables.audit_logs', 'form_builder_audit_logs'));
    }
};
