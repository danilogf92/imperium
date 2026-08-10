<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_milestones', function (Blueprint $table): void {
            $table->timestamp('executed_at')->nullable()->after('percentage');
        });

        Schema::table('project_weekly_activities', function (Blueprint $table): void {
            $table->timestamp('executed_at')->nullable()->after('activity');
        });
    }

    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table): void {
            $table->dropColumn('executed_at');
        });

        Schema::table('project_weekly_activities', function (Blueprint $table): void {
            $table->dropColumn('executed_at');
        });
    }
};
