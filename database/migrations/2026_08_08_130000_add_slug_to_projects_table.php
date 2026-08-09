<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
        });

        $used = [];
        DB::table('projects')->select(['id', 'name'])->orderBy('id')->get()
            ->each(function (object $project) use (&$used): void {
                $base = Str::slug((string) $project->name) ?: 'project';
                $slug = $base;
                $suffix = 2;

                while (isset($used[$slug])) {
                    $slug = $base.'-'.$suffix++;
                }

                $used[$slug] = true;
                DB::table('projects')->where('id', $project->id)->update(['slug' => $slug]);
            });

        Schema::table('projects', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
