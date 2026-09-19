<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('company_owner', function (Blueprint $table): void {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->primary(['company_id', 'owner_id']);
        });

        Schema::create('owner_project', function (Blueprint $table): void {
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->primary(['owner_id', 'project_id']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('sap_order')->nullable()->after('responsible_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn('sap_order');
        });
        Schema::dropIfExists('owner_project');
        Schema::dropIfExists('company_owner');
        Schema::dropIfExists('owners');
    }
};
