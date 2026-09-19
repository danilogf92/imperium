<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['company_id', 'name']);
        });

        DB::table('data')->join('projects', 'projects.id', '=', 'data.project_id')
            ->select('data.id', 'projects.company_id', 'data.supplier')
            ->whereNotNull('data.supplier')->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $name = trim($row->supplier);
                    if ($name !== '') {
                        DB::table('suppliers')->insertOrIgnore([
                            'company_id' => $row->company_id, 'name' => $name,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            }, 'data.id', 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
