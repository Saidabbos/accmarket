<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // Admin Users
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@accmarket.com',
            'password' => $password,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Sellers
        $sellers = [
            ['name' => 'Digital Store Pro', 'email' => 'seller1@accmarket.com'],
            ['name' => 'Account Hub', 'email' => 'seller2@accmarket.com'],
            ['name' => 'Premium Accounts', 'email' => 'seller3@accmarket.com'],
            ['name' => 'Game Keys Store', 'email' => 'seller4@accmarket.com'],
            ['name' => 'Media Accounts', 'email' => 'seller5@accmarket.com'],
        ];

        foreach ($sellers as $sellerData) {
            $seller = User::create([
                'name' => $sellerData['name'],
                'email' => $sellerData['email'],
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $seller->assignRole('seller');
        }

        // Buyers
        $buyers = [
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Mike Johnson', 'email' => 'mike@example.com'],
            ['name' => 'Sarah Williams', 'email' => 'sarah@example.com'],
            ['name' => 'David Brown', 'email' => 'david@example.com'],
            ['name' => 'Emma Davis', 'email' => 'emma@example.com'],
            ['name' => 'Tom Wilson', 'email' => 'tom@example.com'],
            ['name' => 'Lisa Anderson', 'email' => 'lisa@example.com'],
        ];

        foreach ($buyers as $buyerData) {
            $buyer = User::create([
                'name' => $buyerData['name'],
                'email' => $buyerData['email'],
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $buyer->assignRole('buyer');
        }

        // Test accounts (easier to remember)
        $testBuyer = User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer@test.com',
            'password' => $password,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $testBuyer->assignRole('buyer');

        $testSeller = User::create([
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => $password,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $testSeller->assignRole('seller');

        echo "Created " . User::count() . " users\n";
        echo "- Admins: " . User::role('admin')->count() . "\n";
        echo "- Sellers: " . User::role('seller')->count() . "\n";
        echo "- Buyers: " . User::role('buyer')->count() . "\n";
    }
}
