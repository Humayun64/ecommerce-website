<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@amjrglobal.com'],
            [
                'name'     => 'Humayun Kabir',
                'password' => bcrypt('admin1234'),
                'phone'    => '01347419040',
                'is_admin' => true,
            ]
        );

        $this->call([
            SettingSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
