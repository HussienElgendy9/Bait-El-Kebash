<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class CustomerController extends Controller
{
    public function index(){
        $customers = User::where('role', 'customer')->latest()->withCount('order')->paginate(10);
        
        return response()->json([
            'customers' => $customers,
        ]);
    }
    public function show(User $customer){

        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }


        $customer->load('order.orderItems.product');
        
        return response()->json([
            'customer' => $customer,
        ]);
    }

}
