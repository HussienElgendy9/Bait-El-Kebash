<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ([
            ['Admin', 'admin@example.com', 'super_admin', '01000000000'],
            ['Test User', 'test@example.com', 'customer', '01000000001'],
        ] as [$name, $email, $role, $phone]) {
            User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'phone_number' => $phone,
                'address' => 'القاهرة',
                'email_verified_at' => now(),
            ]);
        }

        $this->call(ProductSeeder::class);
    }
}
