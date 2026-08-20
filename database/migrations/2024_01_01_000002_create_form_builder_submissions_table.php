<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('form-builder.tables.submissions', 'form_builder_submissions');
        $forms = config('form-builder.tables.forms', 'form_builder_forms');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) use ($forms): void {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->foreignId('form_id')->constrained($forms)->cascadeOnDelete();
            $blueprint->string('status')->default('new');
            $blueprint->json('data')->nullable();
            $blueprint->string('ip_address', 45)->nullable();
            $blueprint->text('user_agent')->nullable();
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->json('meta')->nullable();
            $blueprint->timestamp('archived_at')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->index('status');
            $blueprint->index(['form_id', 'status']);
            $blueprint->index(['form_id', 'created_at']);
            $blueprint->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('form-builder.tables.submissions', 'form_builder_submissions'));
    }
};
