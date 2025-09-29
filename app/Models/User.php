<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'family',
        'email',
        'phone',
        'national_code',
        'date_of_birth',
        'password',
        'roles',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'roles' => 'array',
        ];
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->roles)) {
                $user->roles = ['ROLE_USER'];
            }
        });
    }

    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SERVICE_WORKER = 'ROLE_SERVICE_WORKER';
    const ROLE_VISITOR= 'ROLE_VISITOR';

    public static $availableRoles = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SERVICE_WORKER,
        self::ROLE_VISITOR,
    ];

    // Helper methods
    public function hasRole($role)
    {
        return in_array($role, $this->roles ?? []);
    }

    public function hasAnyRole($roles)
    {
        return !empty(array_intersect($roles, $this->roles ?? []));
    }

    public function hasAllRoles($roles)
    {
        return empty(array_diff($roles, $this->roles ?? []));
    }

    public function addRole($role)
    {
        $roles = $this->roles ?? [];
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $this->roles = $roles;
        }
        return $this;
    }

    public function removeRole($role)
    {
        $roles = $this->roles ?? [];
        $this->roles = array_values(array_filter($roles, fn($r) => $r !== $role));
        return $this;
    }

    // Specific role checks
    public function isAdmin()
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isServiceWorker()
    {
        return $this->hasRole(self::ROLE_SERVICE_WORKER);
    }
    public function isVisitor()
    {
        return $this->hasRole(self::ROLE_VISITOR);
    }

    public function isUser()
    {
        return $this->hasRole(self::ROLE_USER);
    }


    /**
     * Get all records for this user (if one-to-many)
     */
    public function Business(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Bussiness::class, 'store_user_id');
    }


}
