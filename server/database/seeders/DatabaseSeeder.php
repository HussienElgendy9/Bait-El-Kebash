<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ([
            ['Admin', 'admin@example.com', 'super_admin'],
            ['Test User', 'test@example.com', 'customer'],
        ] as [$name, $email, $role]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'role' => $role,
                'phone_number' => '01000000000',
                'address' => 'القاهرة',
                'email_verified_at' => now(),
            ]);
        }

        // Order matters: the store hardcodes category ids 1–3 (see client categories.ts).
        $products = [
            'خراف' => [
                ['خروف بلدي', 'خروف بلدي مربى على البرسيم والشعير.', 17800],
                ['خروف برقي', 'برقي أصلي، لحم قليل الدهن.', 21500],
                ['خروف رحماني', 'رحماني وزن كبير، مناسب للأضحية.', 24000],
                ['خروف صغير', 'مناسب للعقيقة والولائم الصغيرة.', 12500],
            ],
            'ماعز' => [
                ['ماعز بلدي', 'ماعز بلدي صحته ممتازة.', 9800],
                ['ماعز شامي', 'شامي أصلي، لحم طري.', 14200],
                ['جدي صغير', 'جدي رضيع، لحم خفيف.', 6500],
            ],
            'عجول' => [
                ['عجل بلدي', 'عجل بلدي مسمن، مناسب للاشتراك (٧ أسهم).', 145000],
                ['عجل جاموسي', 'جاموسي وزن كبير.', 168000],
                ['عجل فريزيان', 'فريزيان مستورد، تسمين ممتاز.', 155000],
            ],
        ];

        foreach ($products as $category => $items) {
            $category = Category::create(['name' => $category]);
            foreach ($items as [$name, $description, $price]) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'description' => $description,
                    'unit_price' => $price,
                    'unit' => 'رأس',
                ]);
            }
        }
    }
}
