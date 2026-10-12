<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class CustomerController extends Controller
{
    public function index(Request $request){
        // $customers = User::where('role', 'customer')->latest()->withCount('orders')->paginate(10);
        $query = User::where('role', 'customer')->withCount('orders');
        
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone_number', 'like', "%{$request->search}%");
            });
        }
        $customers = $query->latest()->paginate(10);


        return UserResource::collection($customers);
        // return response()->json([
        //     'customers' => $customers,
        // ]);
    }
    public function show(User $customer){

        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }


        // $customer->load('orders.orderItems.product');

            $orders = $customer->orders()
            ->with('orderItems.product')
            ->latest()
            ->paginate(5);

        $customer->setRelation('orders', $orders);

        $customer->loadCount('orders');

        return new UserResource($customer);
        // return response()->json([
        //     'customer' => $customer,
        // ]);
    }

}
