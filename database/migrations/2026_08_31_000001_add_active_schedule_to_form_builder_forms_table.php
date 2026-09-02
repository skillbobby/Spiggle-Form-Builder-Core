<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('form-builder.tables.forms', 'form_builder_forms');

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table): void {
            if (! Schema::hasColumn($table, 'active_from')) {
                $blueprint->timestamp('active_from')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn($table, 'active_until')) {
                $blueprint->timestamp('active_until')->nullable()->after('active_from');
            }
        });
    }

    public function down(): void
    {
        $table = config('form-builder.tables.forms', 'form_builder_forms');

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table): void {
            if (Schema::hasColumn($table, 'active_until')) {
                $blueprint->dropColumn('active_until');
            }

            if (Schema::hasColumn($table, 'active_from')) {
                $blueprint->dropColumn('active_from');
            }
        });
    }
};
