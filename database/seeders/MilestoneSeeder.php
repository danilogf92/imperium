<?php

namespace Database\Seeders;

use App\Models\Milestone;
use Illuminate\Database\Seeder;

class MilestoneSeeder extends Seeder
{
    public function run(): void
    {
        $milestones = [
            ['name' => 'Work Breakdown Structure', 'code' => 'WBS', 'color' => '#2563EB'],
            ['name' => 'Purchase Order', 'code' => 'PO', 'color' => '#D97706'],
            ['name' => 'Manufacture', 'code' => 'MAN', 'color' => '#7C3AED'],
            ['name' => 'Waiting Material', 'code' => 'WMAT', 'color' => '#EA580C'],
            ['name' => 'Work in Progress', 'code' => 'WP', 'color' => '#0891B2'],
            ['name' => 'Test', 'code' => 'TEST', 'color' => '#4F46E5'],
            ['name' => 'Closed Project', 'code' => 'CLOSED', 'color' => '#16A34A'],
        ];

        foreach ($milestones as $milestone) {
            Milestone::query()->updateOrCreate(
                ['code' => $milestone['code']],
                $milestone
            );
        }
    }
}
