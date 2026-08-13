<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('DaImperium');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        DB::table('brand_settings')->insert([
            'id' => 1,
            'name' => 'DaImperium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_settings');
    }
};
