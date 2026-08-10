<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('handover_certificate_path')->nullable()->after('project_idea_name');
            $table->string('handover_certificate_name')->nullable()->after('handover_certificate_path');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['handover_certificate_path', 'handover_certificate_name']);
        });
    }
};
