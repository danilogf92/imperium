<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_weekly_activities', function (Blueprint $table): void {
            $table->index(
                ['executed_at', 'week_year', 'week_number', 'project_id'],
                'weekly_activities_dashboard_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('project_weekly_activities', function (Blueprint $table): void {
            $table->dropIndex('weekly_activities_dashboard_index');
        });
    }
};
