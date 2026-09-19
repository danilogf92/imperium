<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refuse to discard conflicting or oversized imported values.
        DB::table('data')->select('id', 'order_text', 'denomination', 'description', 'supplier')
            ->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    foreach (['order_text' => 'description', 'denomination' => 'supplier'] as $old => $existing) {
                        if ($row->{$old} === null || $row->{$old} === '') {
                            continue;
                        }
                        if (($row->{$existing} !== null && $row->{$existing} !== '' && $row->{$existing} !== $row->{$old})
                            || ($existing === 'supplier' && mb_strlen($row->{$old}) > 255)) {
                            throw new RuntimeException("Resolve SAP field conflict for Data {$row->id}: {$old} -> {$existing} before migrating.");
                        }
                    }
                }
            });

        DB::transaction(function () {
            foreach (['order_text' => 'description', 'denomination' => 'supplier'] as $old => $existing) {
                DB::table('data')->whereNotNull($old)->where($old, '<>', '')
                    ->where(fn ($query) => $query->whereNull($existing)->orWhere($existing, ''))
                    ->update([$existing => DB::raw($old)]);
            }
        });

        Schema::table('data', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'sap_imported', 'order_year']);
            $table->dropColumn(['order_text', 'denomination', 'sap_imported']);
            $table->index(['project_id', 'sap_order']);
        });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'sap_order']);
            $table->text('order_text')->nullable();
            $table->text('denomination')->nullable();
            $table->boolean('sap_imported')->default(false);
            $table->index(['project_id', 'sap_imported', 'order_year']);
        });
    }
};
