<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PaginationRequest;
use App\Http\Requests\V1\RoleRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\UserManagement;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(PaginationRequest $request)
    {
        return UserResource::collection(User::when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->input('q').'%')->orWhere('email', 'like', '%'.$request->input('q').'%')))->orderByDesc('id')->paginate($request->integer('per_page', 12))->withQueryString());
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(RoleRequest $request, User $user, UserManagement $management)
    {
        Gate::authorize('update', $user);

        return new UserResource($management->changeRole($request->user(), $user, $request->validated('role')));
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        $user->delete();

        return response()->noContent();
    }
}
