<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatistics;

class DashboardController extends Controller
{
    public function index(DashboardStatistics $statistics)
    {
        return response()->json($statistics->legacy());
    }
}
