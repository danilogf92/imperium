<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_rate_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('min_rate', 10, 4)->default(0.3);
            $table->decimal('max_rate', 10, 4)->default(2);
            $table->timestamps();
        });

        DB::table('project_rate_settings')->insert([
            'min_rate' => 0.3,
            'max_rate' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_rate_settings');
    }
};
