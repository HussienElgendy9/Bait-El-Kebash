<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use Illuminate\Http\Request;
// use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::count();
        $orders = Order::count();
        $products = Product::count();
        $categories = Category::count();

// dd($orders);
        // ================= SALES CHART =================

        $salesLabels = [];
        $salesData = [];

        for ($i = 5; $i >= 0; $i--) {

            $date = Carbon::now()->subMonths($i);

            // Month name
            $salesLabels[] = $date->format('M');

            // Total sales for this month
            $salesData[] = OrderItem::whereHas('order', function ($query) use ($date) {

                    $query->where('status','completed')
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month);

                })->sum('price_snapshot');

            $completedOrders = Order::where('status', 'completed')->count();
            $pendingOrders = Order::where('status', 'pending')->count();
            $cancelledOrders = Order::where('status', 'cancelled')->count();

        }


        // ================= CATEGORY CHART =================

        $categoriesData = Category::withCount('products')
            ->orderByDesc('products_count')
            ->get();

        $categoryLabels = $categoriesData->pluck('name');

        $categoryData = $categoriesData->pluck('products_count');


        // ================= RETURN VIEW =================

        return view('admin.index', compact(
            'users',
            'orders',
            'products',
            'categories',
            'salesLabels',
            'salesData',
            'completedOrders',
            'pendingOrders',
            'cancelledOrders',
            'categoryLabels',
            'categoryData'
        ));
    }
}
