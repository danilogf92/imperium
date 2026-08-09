<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('project_idea_path')->nullable()->after('upload_pda');
            $table->string('project_idea_name')->nullable()->after('project_idea_path');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['project_idea_path', 'project_idea_name']);
        });
    }
};
