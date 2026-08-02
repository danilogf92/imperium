<?php

namespace Database\Seeders;

use App\Enums\ProjectPermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ProjectPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProjectPermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
