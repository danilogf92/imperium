<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('order', 20)->nullable()->after('company_id');
            $table->unique(['company_id', 'order'], 'projects_company_order_unique');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropUnique('projects_company_order_unique');
            $table->dropColumn('order');
        });
    }
};
