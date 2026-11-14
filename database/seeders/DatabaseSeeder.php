<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->admin();

        $this->call([
            SettingSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }

    /**
     * The first admin comes from ADMIN_EMAIL and ADMIN_PASSWORD in .env,
     * so no password ever lives in the code or on GitHub.
     *
     * An account that already exists is left completely alone. Running
     * the seeder again on the live site must never reset a password.
     */
    private function admin(): void
    {
        $email    = config('admin.email');
        $password = config('admin.password');

        if (! $email || User::where('email', $email)->exists()) {
            return;
        }

        if (! $password || strlen($password) < 10) {
            $this->command?->warn('No admin created: set ADMIN_PASSWORD (10+ characters) in .env and run db:seed again.');

            return;
        }

        $user = new User();
        $user->forceFill([
            'name'     => config('admin.name') ?: 'Admin',
            'email'    => $email,
            'password' => $password,   // hashed by the model's cast
            'is_admin' => true,
        ])->save();
    }
}
