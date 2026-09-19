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
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        // Recover original authors only where a creation audit exists.
        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')->where('auditable_type', \App\Models\ProjectWeeklyActivity::class)
                ->where('event', 'created')->whereNotNull('user_id')->orderBy('id')
                ->chunkById(500, function ($logs) {
                    foreach ($logs as $log) {
                        if (DB::table('users')->where('id', $log->user_id)->exists()) {
                            DB::table('project_weekly_activities')->where('id', $log->auditable_id)
                                ->whereNull('created_by')->update(['created_by' => $log->user_id]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('project_weekly_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
