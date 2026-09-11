<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatistics;

class DashboardController extends Controller
{
    public function index(DashboardStatistics $statistics)
    {
        $data = $statistics->legacy();

        return view('admin.index', $data + [
            'users' => $data['usersCount'], 'orders' => $data['ordersCount'],
            'products' => $data['productsCount'], 'categories' => $data['categoriesCount'],
        ]);
    }
}
