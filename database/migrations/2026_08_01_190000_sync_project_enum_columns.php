<?php

use App\Enums\InvestmentEnum;
use App\Enums\ProjectStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE projects MODIFY state ENUM(%s) NOT NULL',
            $this->enumValues(ProjectStateEnum::values()),
        ));

        DB::statement(sprintf(
            'ALTER TABLE projects MODIFY investments ENUM(%s) NOT NULL',
            $this->enumValues(InvestmentEnum::values()),
        ));
    }

    public function down(): void
    {
        // Enum values cannot be removed safely when existing projects use them.
    }

    /** @param array<int, string> $values */
    private function enumValues(array $values): string
    {
        return collect($values)
            ->map(fn(string $value): string => DB::getPdo()->quote($value))
            ->implode(', ');
    }
};
