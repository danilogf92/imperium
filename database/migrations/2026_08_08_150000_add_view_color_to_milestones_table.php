<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milestones', function (Blueprint $table): void {
            $table->string('view_color', 7)->nullable()->after('color');
        });

        DB::table('milestones')->update(['view_color' => DB::raw('color')]);
    }

    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table): void {
            $table->dropColumn('view_color');
        });
    }
};
