<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){


        $usersCount=User::count();
        $ordersCount=Order::count();
        $productsCount=Product::count();
        $categoriesCount=Category::count();

        $pendingOrders = Order::where('status','pending')
        ->count();
        $processingOrders = Order::where('status','processing')
        ->count();
        $completedOrders = Order::where('status','completed')
        ->count();
        $cancelledOrders = Order::where('status','cancelled')
        ->count();

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


        }

        $categoriesData = Category::withCount('products')
            ->orderByDesc('products_count')
            ->get();

        $categoryLabels = $categoriesData->pluck('name');

        $categoryData = $categoriesData->pluck('products_count');
        


        return response()->json([
            // 'categories' => $categories, 
            'usersCount'=>$usersCount,
            'ordersCount'=>$ordersCount,
            'productsCount'=>$productsCount,
            'categoriesCount'=>$categoriesCount,
            'salesLabels'=>$salesLabels,
            'salesData'=>$salesData,
            'pendingOrders'=>$pendingOrders,
            'processingOrders'=>$processingOrders,
            'completedOrders'=>$completedOrders,
            'cancelledOrders'=>$cancelledOrders,
            'categoryLabels'=>$categoryLabels,
            'categoryData'=>$categoryData
        ]);

    }
}
