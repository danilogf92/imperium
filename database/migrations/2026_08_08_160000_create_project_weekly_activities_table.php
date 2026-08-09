<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_weekly_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('week_year');
            $table->unsignedTinyInteger('week_number');
            $table->text('activity');
            $table->timestamps();
            $table->unique(['project_id', 'week_year', 'week_number'], 'weekly_activity_project_week_unique');
            $table->index(['week_year', 'week_number'], 'weekly_activity_week_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_weekly_activities');
    }
};
