<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excel_templates', function (Blueprint $table): void {
            $table->string('template_key', 80)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('excel_templates', function (Blueprint $table): void {
            $table->string('template_key', 80)->nullable(false)->change();
        });
    }
};
