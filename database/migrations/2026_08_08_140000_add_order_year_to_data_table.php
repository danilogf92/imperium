<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table): void {
            $table->unsignedSmallInteger('order_year')->nullable()->after('order_no');
            $table->index(['order_year', 'order_no']);
        });

        DB::table('data')->join('projects', 'projects.id', '=', 'data.project_id')
            ->whereNotNull('data.order_no')->where('data.order_no', '<>', '')
            ->select(['data.id', 'data.created_at', 'projects.forecast_start_date'])
            ->orderBy('data.id')->get()->each(function (object $row): void {
                $date = $row->forecast_start_date ?: $row->created_at;
                DB::table('data')->where('id', $row->id)->update([
                    'order_year' => $date ? Carbon::parse($date)->year : now()->year,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table): void {
            $table->dropIndex(['order_year', 'order_no']);
            $table->dropColumn('order_year');
        });
    }
};
