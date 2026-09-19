<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_weekly_activities', function (Blueprint $table) {
            $table->unsignedSmallInteger('week_year')->nullable()->change();
            $table->unsignedTinyInteger('week_number')->nullable()->change();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        });
        DB::transaction(function () {
            DB::table('project_notes')->orderBy('id')->chunkById(500, function ($notes) {
                foreach ($notes as $note) {
                    DB::table('project_weekly_activities')->insert([
                        'project_id' => $note->project_id, 'created_by' => $note->created_by,
                        'activity' => $note->body, 'week_year' => null, 'week_number' => null,
                        'created_at' => $note->created_at, 'updated_at' => $note->updated_at,
                    ]);
                }
            });
        });
        Schema::drop('project_notes');
    }

    public function down(): void
    {
        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['project_id', 'id']);
        });
        DB::transaction(function () {
            DB::table('project_weekly_activities')->whereNull('week_year')->orderBy('id')->chunkById(500, function ($activities) {
                foreach ($activities as $activity) {
                    DB::table('project_notes')->insert([
                        'project_id' => $activity->project_id, 'created_by' => $activity->created_by,
                        'body' => $activity->activity, 'created_at' => $activity->created_at, 'updated_at' => $activity->updated_at,
                    ]);
                }
            });
            DB::table('project_weekly_activities')->whereNull('week_year')->delete();
        });
        Schema::table('project_weekly_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
            $table->unsignedSmallInteger('week_year')->nullable(false)->change();
            $table->unsignedTinyInteger('week_number')->nullable(false)->change();
        });
    }
};
