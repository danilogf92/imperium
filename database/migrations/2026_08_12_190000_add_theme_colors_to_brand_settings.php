<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_settings', function (Blueprint $table): void {
            $table->string('accent_color', 7)->default('#7DB9F1')->after('logo_path');
            $table->string('excel_color', 7)->default('#FDBA74')->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('brand_settings', function (Blueprint $table): void {
            $table->dropColumn(['accent_color', 'excel_color']);
        });
    }
};
