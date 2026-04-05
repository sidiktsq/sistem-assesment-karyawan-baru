<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Admin permissions
            'manage candidates',
            'manage assessments',
            'manage questions',
            'manage candidate assessments',
            'view reports',
            'bulk assign',
            'export data',
            
            // Reviewer permissions
            'review answers',
            'give recommendations',
            'view completed assessments',
            
            // General permissions
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $reviewerRole = Role::firstOrCreate(['name' => 'Reviewer']);

        // Assign permissions to roles
        $superAdminRole->givePermissionTo([
            'manage candidates',
            'manage assessments',
            'manage questions',
            'manage candidate assessments',
            'view reports',
            'bulk assign',
            'export data',
            'view dashboard',
        ]);

        $reviewerRole->givePermissionTo([
            'review answers',
            'give recommendations',
            'view completed assessments',
            'view dashboard',
        ]);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@assessment.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'department' => 'HRD',
                'position' => 'HR Manager',
                'is_active' => true,
            ]
        );
        $admin->assignRole('Super Admin');

        // Create default reviewer user
        $reviewer = User::firstOrCreate(
            ['email' => 'reviewer@assessment.com'],
            [
                'name' => 'Department Head',
                'password' => bcrypt('password'),
                'department' => 'Technical',
                'position' => 'Tech Lead',
                'is_active' => true,
            ]
        );
        $reviewer->assignRole('Reviewer');
    }
}
