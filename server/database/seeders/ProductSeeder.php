<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    // Safe to re-run: existing products are left alone, only missing ones (or missing images) are added.
    // Images: put a photo named after the product's key in database/seeders/images (e.g. sheep-baladi.jpg).
    private const PRODUCTS = [
        'ضاني' => [
            ['sheep-baladi', 'خروف بلدي', 'خروف بلدي مربى على البرسيم والشعير.', 17800],
            ['sheep-barki', 'خروف برقي', 'برقي أصلي، لحم قليل الدهن.', 21500],
            ['sheep-rahmani', 'خروف رحماني', 'رحماني وزن كبير، مناسب للأضحية.', 24000],
            ['sheep-small', 'خروف صغير', 'مناسب للعقيقة والولائم الصغيرة.', 12500],
        ],
        'نيفة' => [
            ['goat-baladi', 'ماعز بلدي', 'ماعز بلدي صحته ممتازة.', 9800],
            ['goat-shami', 'ماعز شامي', 'شامي أصلي، لحم طري.', 14200],
            ['goat-kid', 'جدي صغير', 'جدي رضيع، لحم خفيف.', 6500],
        ],
        'كندوز' => [
            ['calf-baladi', 'عجل بلدي', 'عجل بلدي مسمن، مناسب للاشتراك (٧ أسهم).', 145000],
            ['calf-buffalo', 'عجل جاموسي', 'جاموسي وزن كبير.', 168000],
            ['calf-friesian', 'عجل فريزيان', 'فريزيان مستورد، تسمين ممتاز.', 155000],
        ],
    ];

    public function run(): void
    {
        $this->call(CategorySeeder::class);

        foreach (self::PRODUCTS as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            foreach ($items as [$key, $name, $description, $price]) {
                $product = Product::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $name],
                    ['description' => $description, 'unit_price' => $price, 'unit' => '0.5 kg'],
                );

                if (! $product->image && $image = $this->findImage($key)) {
                    $product->update([
                        'image' => Storage::disk('public')->putFile('products', new File($image)),
                    ]);
                }
            }
        }
    }

    private function findImage(string $key): ?string
    {
        return glob(database_path("seeders/images/{$key}.{jpg,jpeg,png,webp}"), GLOB_BRACE)[0] ?? null;
    }
}
