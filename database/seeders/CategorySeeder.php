<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Surgery', 'slug' => 'surgery', 'icon' => 'scalpel', 'sort_order' => 1],
            ['name' => 'Internal Medicine', 'slug' => 'internal-medicine', 'icon' => 'stethoscope', 'sort_order' => 2],
            ['name' => 'Pediatrics', 'slug' => 'pediatrics', 'icon' => 'baby', 'sort_order' => 3],
            ['name' => 'Obstetrics', 'slug' => 'obstetrics', 'icon' => 'heart-pulse', 'sort_order' => 4],
            ['name' => 'Cardiology', 'slug' => 'cardiology', 'icon' => 'heart', 'sort_order' => 5],
            ['name' => 'Dermatology', 'slug' => 'dermatology', 'icon' => 'hand', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
