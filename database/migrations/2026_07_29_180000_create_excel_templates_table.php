<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('template_key', 80)->unique();
            $table->string('category', 40)->index();
            $table->text('description')->nullable();
            $table->string('disk', 40)->default('local');
            $table->string('file_path');
            $table->string('original_file_name');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('excel_templates')->insert([
            [
                'name' => 'Order Download Template',
                'template_key' => 'order_export',
                'category' => 'orders',
                'description' => 'Template used to generate an Excel file for each project order.',
                'disk' => 'local',
                'file_path' => 'excel-templates/FormatoODT.xlsx',
                'original_file_name' => 'FormatoODT.xlsx',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Project Data Import Template',
                'template_key' => 'project_data_import',
                'category' => 'project_data',
                'description' => 'Approved blank workbook for importing data into a project.',
                'disk' => 'local',
                'file_path' => 'excel-templates/Project-Data-Import-Template.xlsx',
                'original_file_name' => 'Project-Data-Import-Template.xlsx',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_templates');
    }
};
