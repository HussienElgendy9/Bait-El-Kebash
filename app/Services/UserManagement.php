<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserManagement
{
    public function changeRole(User $actor, User $user, string $role): User
    {
        return DB::transaction(function () use ($actor, $user, $role) {
            if (DB::getDriverName() === 'sqlite') {
                DB::table('users')->where('role', 'admin')->update(['id' => DB::raw('id')]);
            }
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_if($actor->id === $target->id, 403, 'You cannot change your own role.');
            abort_if($target->isAdmin() && $role !== 'admin' && $admins->count() <= 1, 409, 'The last administrator cannot be demoted.');
            $target->role = $role;
            $target->save();

            return $target;
        }, 3);
    }
}
