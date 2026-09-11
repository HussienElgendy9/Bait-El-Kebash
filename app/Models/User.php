<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< Updated upstream
=======
use Laravel\Sanctum\HasApiTokens;
>>>>>>> Stashed changes

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'role',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function order()
    {
        return $this->hasmany(Order::class);
    }
<<<<<<< Updated upstream
=======

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            abort_if($user->orders()->exists(), 409, 'Users with orders cannot be deleted.');
            abort_if($user->isAdmin(), 409, 'Demote the administrator before deletion.');
        });
    }

    public function phoneVerifications()
    {
        return $this->hasMany(PhoneVerification::class);
    }
>>>>>>> Stashed changes
}
