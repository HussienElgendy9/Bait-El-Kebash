<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['admin', 'super_admin'])->latest()->paginate(10);
        
        return response()->json([
            'users' => $users,
        ]);
    }
    public function show(User $admin)
    {
        if (!in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json([
                'message' => 'Admin user not found.',
            ], 404);
        }

        return response()->json([
            'user' => $admin,
        ]);
    }
}
