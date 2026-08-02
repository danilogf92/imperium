<?php

namespace Tests\Feature;

use App\Enums\AuditPermissionEnum;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Milestone;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AuditPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_changes_are_recorded_with_user_and_changed_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        AuditLog::query()->delete();

        $milestone = Milestone::query()->create([
            'name' => 'Purchase Order',
            'code' => 'PO',
            'color' => '#2563eb',
        ]);

        $milestone->update(['color' => '#16a34a']);
        $milestone->delete();

        $logs = AuditLog::query()->orderBy('id')->get();

        $this->assertCount(3, $logs);
        $this->assertSame(['created', 'updated', 'deleted'], $logs->pluck('event')->all());
        $this->assertTrue($logs->every(fn (AuditLog $log): bool =>
            $log->user_id === $user->getKey()));
        $this->assertSame('#2563eb', $logs[1]->old_values['color']);
        $this->assertSame('#16a34a', $logs[1]->new_values['color']);
    }

    public function test_audit_resource_requires_its_filament_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->assertFalse(AuditLogResource::canViewAny());

        $permission = Permission::findOrCreate(
            AuditPermissionEnum::View->value,
            'web',
        );
        $user->givePermissionTo($permission);

        $this->assertTrue(AuditLogResource::canViewAny());
        $this->assertFalse(AuditLogResource::canCreate());
    }

    public function test_project_managers_receive_audit_access_but_viewers_do_not(): void
    {
        $manager = User::factory()->create();
        $viewer = User::factory()->create();
        $managerRole = Role::query()->create([
            'name' => 'CIESA PROJECT MANAGER',
            'guard_name' => 'web',
        ]);
        $viewerRole = Role::query()->create([
            'name' => 'MANTA VIEWER',
            'guard_name' => 'web',
        ]);

        $manager->assignRole($managerRole);
        $viewer->assignRole($viewerRole);

        $this->seed(AuditPermissionSeeder::class);

        $this->assertTrue($manager->fresh()->can(AuditPermissionEnum::View->value));
        $this->assertFalse($viewer->fresh()->can(AuditPermissionEnum::View->value));
    }
}
