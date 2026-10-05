<?php

namespace Database\Seeders;

use App\Models\PlatformPaymentMethod;
use Illuminate\Database\Seeder;

class PlatformPaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'type'           => 'bank',
                'label'          => 'NIC Asia Bank Transfer',
                'account_name'   => 'TravelAI Nepal Pvt. Ltd.',
                'account_number' => '1234567890123456',
                'bank_name'      => 'NIC Asia Bank',
                'identifier'     => null,
                'currency'       => 'NPR',
                'instructions'   => 'Transfer subscription amount to the account above. Use your subscription ID as reference.',
                'is_active'      => true,
                'sort_order'     => 1,
            ],
            [
                'type'           => 'esewa',
                'label'          => 'eSewa Wallet',
                'account_name'   => 'TravelAI Nepal',
                'account_number' => '9800000001',
                'identifier'     => 'TRAVELAI',
                'bank_name'      => null,
                'currency'       => 'NPR',
                'instructions'   => 'Send payment to eSewa ID. Save the transaction screenshot as proof.',
                'is_active'      => true,
                'sort_order'     => 2,
            ],
            [
                'type'           => 'khalti',
                'label'          => 'Khalti Wallet',
                'account_name'   => 'TravelAI Nepal',
                'account_number' => '9800000002',
                'identifier'     => 'TRAVELAI',
                'bank_name'      => null,
                'currency'       => 'NPR',
                'instructions'   => 'Pay via Khalti. Note the transaction reference for verification.',
                'is_active'      => true,
                'sort_order'     => 3,
            ],
        ];

        foreach ($methods as $data) {
            PlatformPaymentMethod::updateOrCreate(
                ['label' => $data['label']],
                $data
            );
        }

        $this->command->info('✅ PlatformPaymentMethodSeeder: ' . count($methods) . ' methods');
    }
}