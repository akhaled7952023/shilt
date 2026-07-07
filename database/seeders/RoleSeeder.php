<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [];

        foreach (config('permissions_ar') as $permission => $value) {
            $permissions[] = $permission;
        }

        Role::create([
            'name' => 'مدير',
            'permissions' => $permissions,
        ]);
    }
}
