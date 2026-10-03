<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'position',
        'department',
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
        ];
    }

    public static function moduleOptions(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'office-selection' => 'Conduct Preventive Maintenance',
            'preventive-maintenance-form' => 'PM Form',
            'pm-records-list' => 'PM Records',
            'office-manager' => 'Manage Offices',
            'pm-schedule-manager' => 'PM Schedule Manager',
            'account-manager' => 'Account Manager',
            'position-manager' => 'Position Manager',
        ];
    }

    public function assignedPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position', 'name');
    }

    public function isAdmin(): bool
    {
        return $this->hasModuleAccess('account-manager');
    }

    public function hasModuleAccess(string $module): bool
    {
        return $this->assignedPosition?->hasModuleAccess($module) ?? false;
    }

    public function firstAccessibleRouteName(): string
    {
        $routes = [
            'dashboard' => 'dashboard',
            'office-selection' => 'office-selection',
            'pm-records-list' => 'pm-records-list',
            'office-manager' => 'office-manager',
            'pm-schedule-manager' => 'pm-schedule-manager',
            'account-manager' => 'admin.accounts',
            'position-manager' => 'admin.positions',
        ];

        foreach ($routes as $module => $route) {
            if ($this->hasModuleAccess($module)) {
                return $route;
            }
        }

        return 'profile';
    }
}
