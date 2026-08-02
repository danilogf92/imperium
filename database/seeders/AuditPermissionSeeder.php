<?php

namespace Database\Seeders;

use App\Enums\AuditPermissionEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AuditPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::findOrCreate(
            AuditPermissionEnum::View->value,
            'web',
        );

        Role::query()
            ->where(function ($query): void {
                $query
                    ->whereIn('name', ['admin', 'administrator', 'super-admin'])
                    ->orWhere('name', 'like', '%PROJECT MANAGER');
            })
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
