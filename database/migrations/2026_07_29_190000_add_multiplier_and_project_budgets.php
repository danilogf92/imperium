<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->decimal('multiplier', 14, 6)->default(1)->after('company_code');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->decimal('base_budgeted', 18, 2)->default(0)->after('rate');
            $table->decimal('budgeted', 18, 2)->default(0)->after('base_budgeted');
            $table->decimal('base_budgeted_euros', 18, 2)->default(0)->after('budgeted');
            $table->decimal('budgeted_euros', 18, 2)->default(0)->after('base_budgeted_euros');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn([
                'base_budgeted',
                'budgeted',
                'base_budgeted_euros',
                'budgeted_euros',
            ]);
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn('multiplier');
        });
    }
};
