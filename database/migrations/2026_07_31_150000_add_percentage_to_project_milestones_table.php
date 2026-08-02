<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_milestones', function (Blueprint $table): void {
            $table->decimal('percentage', 5, 2)->default(0)->after('sequence');
        });
    }

    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table): void {
            $table->dropColumn('percentage');
        });
    }
};
