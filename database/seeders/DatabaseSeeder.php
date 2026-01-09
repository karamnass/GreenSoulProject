<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //انشاء صلاحيات و ادوار
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // إنشاء مستخدم أدمن تجريبي
        $adminRole = Role::where('role_name', 'admin')->first();

        if ($adminRole) {
            User::updateOrCreate(
                ['phone' => '0999999999'], // غيّر الرقم كما تحب
                [
                    'name'              => 'Super Admin',
                    'is_active'         => true,
                    'phone_verified_at' => now(),
                    'role_id'           => $adminRole->id,
                ]
            );
        }
    }

}
