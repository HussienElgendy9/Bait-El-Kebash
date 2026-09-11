<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.show', compact('user'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::id() == $id) {
            return redirect()->route('admin.users.index')
                ->with('failed', 'You cant change your role.');
        } else {
            $request->validate([
                'role' => ['required', Rule::in(['admin', 'customer'])],
            ]);
            $user = User::findOrFail($id);
            app(UserManagement::class)->changeRole($request->user(), $user, $request->role);

            return redirect()->route('admin.users.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::id() == $id) {
            return redirect()->route('admin.users.index')
                ->with('failed', 'You cant delete your account.');
        } else {
            // $user = User::findOrFail($id);
            $user = User::with('orders')->findOrFail($id);
            // $user = User::with([
            //     'orders',
            //     'orders.items',
            //     ])->findOrFail($id);
            if (Auth::id() == $user->id || $user->role == 'admin' || $user->orders()->where('status', 'pending')->exists()) {
                return redirect()->route('admin.users.index')
                    ->with('failed', 'User could not be deleted.');
            }
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        }
    }
}
