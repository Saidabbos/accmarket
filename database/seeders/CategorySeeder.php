<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Gaming Accounts' => [
                'Steam Accounts',
                'PlayStation Accounts',
                'Xbox Accounts',
                'Epic Games Accounts',
            ],
            'Streaming Services' => [
                'Netflix Accounts',
                'Spotify Accounts',
                'Disney+ Accounts',
                'HBO Max Accounts',
            ],
            'Social Media' => [
                'Instagram Accounts',
                'Twitter Accounts',
                'TikTok Accounts',
                'Facebook Accounts',
            ],
            'Software Licenses' => [
                'Windows Keys',
                'Office Keys',
                'Antivirus Keys',
                'VPN Subscriptions',
            ],
            'Gift Cards' => [
                'Amazon Gift Cards',
                'iTunes Gift Cards',
                'Google Play Cards',
                'Steam Gift Cards',
            ],
        ];

        $sortOrder = 0;
        foreach ($categories as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'description' => "Browse our collection of {$parentName}",
                    'parent_id' => null,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'description' => "High quality {$childName} available",
                        'parent_id' => $parent->id,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
