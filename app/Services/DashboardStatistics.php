<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class DashboardStatistics
{
    public function get(): array
    {
        $sales = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $cents = 0;
            foreach (OrderItem::whereHas('order', fn ($query) => $query->where('status', 'completed')->whereBetween('created_at', [$date, $date->copy()->endOfMonth()]))->cursor() as $item) {
                $cents += Money::cents($item->price_snapshot);
            }
            $sales[] = ['month' => $date->format('Y-m'), 'total' => Money::decimal($cents)];
        }

        return [
            'users' => User::count(), 'orders' => Order::count(), 'products' => Product::count(), 'categories' => Category::count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'sales' => $sales,
            'category_products' => Category::withCount('products')->orderByDesc('products_count')->get()->map(fn ($category) => ['id' => $category->id, 'name' => $category->name, 'products_count' => $category->products_count])->all(),
        ];
    }

    public function legacy(): array
    {
        $data = $this->get();

        return [
            'usersCount' => $data['users'], 'ordersCount' => $data['orders'],
            'productsCount' => $data['products'], 'categoriesCount' => $data['categories'],
            'completedOrders' => $data['completed_orders'], 'pendingOrders' => $data['pending_orders'],
            'cancelledOrders' => $data['cancelled_orders'],
            'salesLabels' => array_map(fn ($row) => Carbon::parse($row['month'].'-01')->format('M'), $data['sales']),
            'salesData' => array_column($data['sales'], 'total'),
            'categoryLabels' => array_column($data['category_products'], 'name'),
            'categoryData' => array_column($data['category_products'], 'products_count'),
        ];
    }
}
