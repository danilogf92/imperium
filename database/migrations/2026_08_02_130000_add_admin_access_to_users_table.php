<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('can_access_admin')->default(false)->after('is_active')->index();
        });

        // Preserve access for administrators that existed before this rule.
        DB::table('users')->update(['can_access_admin' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('can_access_admin');
        });
    }
};
