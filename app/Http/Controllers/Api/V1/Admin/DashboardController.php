<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Money;

class DashboardController extends Controller
{
    public function index()
    {
        $sales = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $cents = 0;
            foreach (OrderItem::whereHas('order', fn ($q) => $q->where('status', 'completed')->whereBetween('created_at', [$date, $date->copy()->endOfMonth()]))->cursor() as $item) {
                $cents += Money::cents($item->price_snapshot);
            }
            $sales[] = ['month' => $date->format('Y-m'), 'total' => Money::decimal($cents)];
        }

        return response()->json(['data' => [
            'users' => User::count(), 'orders' => Order::count(), 'products' => Product::count(), 'categories' => Category::count(),
            'completed_orders' => Order::where('status', 'completed')->count(), 'pending_orders' => Order::where('status', 'pending')->count(), 'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'sales' => $sales, 'category_products' => Category::withCount('products')->orderByDesc('products_count')->get()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'products_count' => $c->products_count]),
        ]]);
    }
}
