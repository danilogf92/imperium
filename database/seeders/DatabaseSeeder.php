<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            CitySeeder::class,
            ProjectPermissionSeeder::class,
            MilestoneSeeder::class,
        ]);

        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->make([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])->getAttributes(),
        );

        $cityId = City::query()
            ->where('city_code', 'GYE')
            ->value('id') ?? City::query()->value('id');

        foreach ([
            'CIESA' => 'CIESA',
            'GRALCO' => 'GRALCO',
            'SEAFMAN' => 'SEAFMAN',
        ] as $code => $name) {
            $company = Company::query()->updateOrCreate(
                ['company_code' => $code],
                [
                    'city_id' => $cityId,
                    'company_name' => $name,
                ],
            );

            $role = Role::query()->updateOrCreate(
                [
                    'name' => "PROJECT MANAGER {$code}",
                    'guard_name' => 'web',
                ],
                ['company_id' => $company->getKey()],
            );

            $role->syncPermissions(\Spatie\Permission\Models\Permission::all());
            $user->assignRole($role);
        }

        $this->call(AuditPermissionSeeder::class);
    }
}
