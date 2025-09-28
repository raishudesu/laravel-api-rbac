<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            ['name' => 'Create Posts', 'slug' => 'create-posts', 'description' => 'Can create new posts'],
            ['name' => 'Read Posts', 'slug' => 'read-posts', 'description' => 'Can view posts'],
            ['name' => 'Update Posts', 'slug' => 'update-posts', 'description' => 'Can edit posts'],
            ['name' => 'Delete Posts', 'slug' => 'delete-posts', 'description' => 'Can delete posts'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'Can manage user accounts'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'description' => 'Can manage roles and permissions'],
            ['name' => 'View Analytics', 'slug' => 'view-analytics', 'description' => 'Can view system analytics'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access',
                'permissions' => Permission::all()->pluck('id')->toArray(),
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access',
                'permissions' => Permission::whereIn('slug', [
                    'create-posts',
                    'read-posts',
                    'update-posts',
                    'delete-posts',
                    'manage-users',
                    'view-analytics',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Content management access',
                'permissions' => Permission::whereIn('slug', [
                    'create-posts',
                    'read-posts',
                    'update-posts',
                    'delete-posts',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Basic user access',
                'permissions' => Permission::whereIn('slug', [
                    'read-posts',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            $role->permissions()->sync($permissions);
        }
    }
}
