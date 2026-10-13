<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public const NAMES = ['خراف', 'ماعز', 'عجول'];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
