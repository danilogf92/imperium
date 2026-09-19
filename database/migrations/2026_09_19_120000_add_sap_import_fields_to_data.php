<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->text('order_text')->nullable();
            $table->string('sap_order')->nullable();
            $table->text('denomination')->nullable();
            $table->date('accounting_date')->nullable();
            $table->date('document_date')->nullable();
            $table->boolean('sap_imported')->default(false);
            $table->index(['project_id', 'sap_imported', 'order_year']);
        });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'sap_imported', 'order_year']);
            $table->dropColumn(['order_text', 'sap_order', 'denomination', 'accounting_date', 'document_date', 'sap_imported']);
        });
    }
};
