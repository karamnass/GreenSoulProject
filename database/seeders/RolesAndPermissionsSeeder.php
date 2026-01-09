<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // 1) إنشاء الأدوار
        $adminRole = Role::firstOrCreate(
            ['role_name' => 'admin'],
            ['guard_name' => 'api']
        );

        $userRole = Role::firstOrCreate(
            ['role_name' => 'user'],
            ['guard_name' => 'api']
        );

        // 2) إنشاء الصلاحيات
        $permissions = [
            'manage_users',
            'manage_tips',
            'manage_complaints',
            'manage_notifications',
            'manage_plant_database',
            'view_analytics',
            'manage_system_settings',
        ];

        $permissionIds = [];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(
                ['permission_name' => $perm],
                ['guard_name' => 'api']
            );

            $permissionIds[] = $permission->id;
        }

        // 3) ربط جميع الصلاحيات بالـ admin
        foreach ($permissionIds as $pid) {
            RolePermission::firstOrCreate([
                'role_id'       => $adminRole->id,
                'permission_id' => $pid,
            ]);
        }

        
    }
}
