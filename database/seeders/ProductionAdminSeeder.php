<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionAdminSeeder extends Seeder
{
    /**
     * Idempotent admin user creation.
     * Env-driven credentials (no hardcoded values).
     * Skips if env vars missing (safe default).
     */
    public function run(): void
    {
        // Defaults: Owner (Parashar Regmi) — can be overridden via .env
        $email    = env('ADMIN_EMAIL',    'parasharregmi@gmail.com');
        $password = env('ADMIN_PASSWORD', 'Himalayan@1980');
        $name     = env('ADMIN_NAME',     'Parashar Regmi');
        $phone    = env('ADMIN_PHONE',    '9761762036');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'phone'             => $phone,
                'password'          => Hash::make($password),
                'role'              => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Super Admin ready: ' . $email);
    }
}