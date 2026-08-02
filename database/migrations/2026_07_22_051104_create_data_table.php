<?php

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
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('area')->nullable();
            $table->string('group_1')->nullable();
            $table->string('group_2')->nullable();
            $table->text('description')->nullable();
            $table->string('general_classification')->nullable();
            $table->string('item_type')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('global_price', 10, 2)->default(0);
            $table->string('stage')->nullable();

            $table->decimal('real_value', 10, 2)->default(0);
            $table->timestamp('real_value_changed_at')->nullable();

            $table->decimal('committed', 10, 2)->default(0);
            $table->timestamp('committed_changed_at')->nullable();

            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamp('percentage_changed_at')->nullable();

            $table->decimal('executed_dollars', 10, 2)->default(0);
            $table->decimal('executed_euros', 10, 2)->default(0);
            $table->timestamp('executed_changed_at')->nullable();

            $table->string('supplier')->nullable();
            $table->string('code')->nullable();
            $table->string('order_no')->nullable();
            $table->string('input_num')->nullable();
            $table->text('observations')->nullable();

            $table->decimal('booked', 10, 2)->default(0);
            $table->timestamp('booked_changed_at')->nullable();

            $table->decimal('global_price_euros', 10, 2)->default(0);
            $table->decimal('real_value_euros', 10, 2)->default(0);
            $table->decimal('booked_euros', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};
