<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettlementPermissionSeeder extends Seeder
{
    /**
     * Seed Full & Final Settlement permissions and assign them to roles.
     */
    public function run(): void
    {
        $permissions = [
            'Manage Settlement',
            'Create Settlement',
            'Edit Settlement',
            'Delete Settlement',
            'Show Settlement',
            'Sign Settlement',
            'Send Settlement Mail',
        ];

        // Create permissions with 'web' guard
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Full management access for Admin & HR roles
        $adminRoles = ['super admin', 'company', 'hr', 'HR'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Read & Sign access for employee roles
        $employeeRoles = ['employee', 'Employee', 'Salon Staff'];
        foreach ($employeeRoles as $empRole) {
            $role = Role::where('name', $empRole)->first();
            if ($role) {
                $role->givePermissionTo(['Show Settlement', 'Sign Settlement']);
            }
        }
    }
}
