<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserRoleController extends Controller
{
    public function updateRole(Request $request, User $user){
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,customer'],
        ]);
        if($validated['role'] === $user->role){
            return response()->json([
                'message' => 'User already has the specified role.',
            ], 400);
        }
        if ($user->isSuperAdmin()) {
            return response()->json([
                'message' => 'Super admin role cannot be changed.',
            ], 403);
        }

        $user->update([
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'User role updated successfully.',
            'user' => new UserResource($user),
        ]);
    }
}
