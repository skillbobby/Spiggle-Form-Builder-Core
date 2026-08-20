<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('form-builder.tables.forms', 'form_builder_forms');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->string('base_path')->unique();
            $blueprint->text('description')->nullable();
            $blueprint->string('container_type')->default('single');
            $blueprint->string('schema_version')->default('1.0');
            $blueprint->json('schema')->nullable();
            $blueprint->json('settings')->nullable();
            $blueprint->boolean('is_published')->default(false);
            $blueprint->boolean('is_active')->default(true);
            $blueprint->text('success_message')->nullable();
            $blueprint->string('redirect_url')->nullable();
            $blueprint->json('notify_emails')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->index(['is_published', 'is_active']);
            $blueprint->index('container_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('form-builder.tables.forms', 'form_builder_forms'));
    }
};
