<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = collect([
            'base_budgeted',
            'budgeted',
            'base_budgeted_euros',
            'budgeted_euros',
        ])->filter(fn (string $column): bool => Schema::hasColumn('projects', $column))->all();

        if ($columns !== []) {
            Schema::table('projects', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->decimal('base_budgeted', 18, 2)->default(0)->after('rate');
            $table->decimal('budgeted', 18, 2)->default(0)->after('base_budgeted');
            $table->decimal('base_budgeted_euros', 18, 2)->default(0)->after('budgeted');
            $table->decimal('budgeted_euros', 18, 2)->default(0)->after('base_budgeted_euros');
        });
    }
};
