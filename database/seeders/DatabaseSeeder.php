<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "\n";
        echo str_repeat('=', 60) . "\n";
        echo "  SEEDING ACCMARKET DATABASE\n";
        echo str_repeat('=', 60) . "\n\n";

        // Seed roles first
        echo "→ Creating roles...\n";
        $this->call(RoleSeeder::class);

        // Create users
        echo "\n→ Creating users...\n";
        $this->call(UserSeeder::class);

        // Seed categories and products
        echo "\n→ Creating categories...\n";
        $this->call(CategorySeeder::class);

        echo "\n→ Creating products...\n";
        $this->call(ProductSeeder::class);

        echo "\n";
        echo str_repeat('=', 60) . "\n";
        echo "  DATABASE SEEDING COMPLETED!\n";
        echo str_repeat('=', 60) . "\n";
        echo "\nTest Accounts:\n";
        echo "  Admin:  admin@accmarket.com  / password\n";
        echo "  Seller: seller@test.com      / password\n";
        echo "  Buyer:  buyer@test.com       / password\n\n";
    }
}
