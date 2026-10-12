<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    // Order matters on a fresh database: the store hardcodes category ids 1–3 (see client categories.ts).
    public const NAMES = ['خراف', 'ماعز', 'عجول'];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
