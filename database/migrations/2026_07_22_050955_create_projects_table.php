<?php

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('responsible_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');

            $table->string('pda_code')
                ->unique();

            $table->decimal('rate', 10, 2)
                ->default(0);

            $table->enum(
                'state',
                ProjectStateEnum::values()
            );

            $table->enum(
                'investments',
                InvestmentEnum::values()
            );

            $table->enum(
                'justification',
                ProjectJustificationEnum::values()
            );

            $table->enum(
                'classification_of_investments',
                InvestmentClassificationEnum::values()
            );

            $table->boolean('data_uploaded')
                ->default(false);

            $table->date('quartile_date')
                ->nullable();

            $table->date('forecast_start_date')
                ->nullable();

            $table->date('forecast_end_date')
                ->nullable();

            $table->string('file_name')
                ->nullable();

            $table->string('upload_pda')
                ->nullable();

            $table->date('approve_date')
                ->nullable();

            $table->date('close_date')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
