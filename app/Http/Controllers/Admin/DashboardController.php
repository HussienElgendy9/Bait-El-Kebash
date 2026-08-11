<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        // $users = User::all();
        // $order = Order::all();
        // $orderItem = OrderItem::all();
        // $product = Product::all();
        // $category = Category::all();

        $users = User::count();
        $order = Order::count();
        $product = Product::count();
        $category = Category::count();

        return view('admin.index',compact('users', 'order', 'product', 'category'));
    }
// return view admin.dashboard compact('users', 'order', 'orderItem', 'product', 'category');    }
}
