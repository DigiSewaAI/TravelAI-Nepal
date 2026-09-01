<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * All users password: Himalayan@1980
     */
    public function run(): void
    {
        // ==========================================
        // 1. SUPER ADMIN
        // ==========================================
        User::updateOrCreate(
            ['email' => 'parasharregmi@gmail.com'],
            [
                'name' => 'Parashar Regmi',
                'phone' => '9761762036',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'super_admin',
            ]
        );
        $this->command->info('✅ Super Admin created: Parashar Regmi');

        // ==========================================
        // 2. PROVIDER OWNER
        // ==========================================
        User::updateOrCreate(
            ['email' => 'anjuregmimesh@gmail.com'],
            [
                'name' => 'Anju Regmi',
                'phone' => '9812345678',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider Owner created: Anju Regmi (The Himalayan Journey)');

        // ==========================================
        // 3. TREKKER / TRAVELER
        // ==========================================
        User::updateOrCreate(
            ['email' => 'shresthaxok@gmail.com'],
            [
                'name' => 'John Adreson',
                'phone' => '9812345679',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'traveler',
            ]
        );
        $this->command->info('✅ Traveler created: John Adreson');

        // ==========================================
        // 4. EXTRA PROVIDERS (Imaginary)
        // ==========================================

        // 4.1 Trekking Agency
        User::updateOrCreate(
            ['email' => 'info@everestbasecamptrek.com'],
            [
                'name' => 'Everest Base Camp Trek',
                'phone' => '9812345680',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider created: Everest Base Camp Trek');

        // 4.2 Tour Agency
        User::updateOrCreate(
            ['email' => 'info@nepalheritagetours.com'],
            [
                'name' => 'Nepal Heritage Tours',
                'phone' => '9812345681',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider created: Nepal Heritage Tours');

        // 4.3 Hotel
        User::updateOrCreate(
            ['email' => 'info@himalayanhotel.com'],
            [
                'name' => 'Himalayan Hotel & Resort',
                'phone' => '9812345682',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider created: Himalayan Hotel & Resort');

        // 4.4 Activity Provider (Paragliding, Rafting)
        User::updateOrCreate(
            ['email' => 'info@adventurenepal.com'],
            [
                'name' => 'Adventure Nepal Activities',
                'phone' => '9812345683',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider created: Adventure Nepal Activities');

        // 4.5 Guide & Porter Service
        User::updateOrCreate(
            ['email' => 'info@professionalguides.com'],
            [
                'name' => 'Professional Guides Nepal',
                'phone' => '9812345684',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider created: Professional Guides Nepal');

        // 4.6 Transport Provider
        User::updateOrCreate(
            ['email' => 'info@himalayantransport.com'],
            [
                'name' => 'Himalayan Transport Service',
                'phone' => '9812345685',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info('✅ Provider created: Himalayan Transport Service');

        // ==========================================
        // 5. EXTRA TRAVELERS (Imaginary)
        // ==========================================

        // 5.1 Traveler - Maria
        User::updateOrCreate(
            ['email' => 'maria.smith@gmail.com'],
            [
                'name' => 'Maria Smith',
                'phone' => '9812345686',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'traveler',
            ]
        );
        $this->command->info('✅ Traveler created: Maria Smith');

        // 5.2 Traveler - David
        User::updateOrCreate(
            ['email' => 'david.chen@gmail.com'],
            [
                'name' => 'David Chen',
                'phone' => '9812345687',
                'password' => Hash::make('Himalayan@1980'),
                'role' => 'traveler',
            ]
        );
        $this->command->info('✅ Traveler created: David Chen');

        // ==========================================
        // 6. SUMMARY
        // ==========================================
        $this->command->info('🎉 User Seeder Complete!');
        $this->command->info('📊 Total Users Created: 11');
        $this->command->info('🔑 All users password: Himalayan@1980');
        $this->command->info('📧 Super Admin: parasharregmi@gmail.com');
        $this->command->info('📧 Provider: anjuregmimesh@gmail.com');
        $this->command->info('📧 Traveler: shresthaxok@gmail.com');
    }
}