<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('cycle_year');
            $table->unsignedInteger('sequence');
            $table->timestamps();

            $table->unique(['project_id', 'sequence']);
            $table->index(['project_id', 'cycle_year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
