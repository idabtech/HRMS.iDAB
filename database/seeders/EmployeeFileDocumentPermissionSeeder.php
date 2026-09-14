<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class EmployeeFileDocumentPermissionSeeder extends Seeder
{
    /**
     * Seed Employee File Document permissions and assign ONLY to Company role & users.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [
            'Manage Employee File Document',
            'Create Employee File Document',
            'Edit Employee File Document',
            'Delete Employee File Document',
            'View Employee File Document',
            'Download Employee File Document',
        ];

        // Ensure permissions exist
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Revoke Employee File Document permissions from all non-company roles
        $allRoles = Role::all();
        foreach ($allRoles as $role) {
            if ($role->name !== 'company') {
                foreach ($allPermissions as $p) {
                    if ($role->hasPermissionTo($p)) {
                        $role->revokePermissionTo($p);
                    }
                }
            }
        }

        // Assign ALL permissions exclusively to 'company' role
        $companyRole = Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
        if ($companyRole) {
            foreach ($allPermissions as $p) {
                if (!$companyRole->hasPermissionTo($p)) {
                    $companyRole->givePermissionTo($p);
                }
            }
        }

        // Revoke permissions directly assigned to non-company users
        $nonCompanyUsers = User::where('type', '!=', 'company')->get();
        foreach ($nonCompanyUsers as $user) {
            foreach ($allPermissions as $p) {
                if ($user->hasPermissionTo($p)) {
                    $user->revokePermissionTo($p);
                }
            }
        }

        // Directly assign permissions to company type users
        $companyUsers = User::where('type', 'company')->get();
        foreach ($companyUsers as $cUser) {
            foreach ($allPermissions as $p) {
                if (!$cUser->hasPermissionTo($p)) {
                    $cUser->givePermissionTo($p);
                }
            }
        }

        // Reset cache again after changes
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

