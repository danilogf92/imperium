<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excel_templates', function (Blueprint $table): void {
            $table->boolean('is_global')->default(true);
        });
        Schema::create('company_excel_template', function (Blueprint $table): void {
            $table->foreignId('excel_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->primary(['excel_template_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_excel_template');
        Schema::table('excel_templates', fn (Blueprint $table) => $table->dropColumn('is_global'));
    }
};
