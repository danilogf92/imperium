<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_weekly_activities', function (Blueprint $table): void {
            $table->index('project_id', 'weekly_activity_project_index');
            $table->dropUnique('weekly_activity_project_week_unique');
            $table->index(['project_id', 'week_year', 'week_number'], 'weekly_activity_project_week_index');
        });
    }

    public function down(): void
    {
        Schema::table('project_weekly_activities', function (Blueprint $table): void {
            $table->dropIndex('weekly_activity_project_week_index');
            $table->unique(['project_id', 'week_year', 'week_number'], 'weekly_activity_project_week_unique');
            $table->dropIndex('weekly_activity_project_index');
        });
    }
};
