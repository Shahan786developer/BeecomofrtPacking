<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'role',
        'password',
        'ic_card',
        'phone1',
        'phone2',
        'phone3',
        'address',
        'is_active',
    ];

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin(): bool
    {
        if (isset($this->role) && in_array(strtolower($this->role), ['superadmin', 'super_admin'])) {
            return true;
        }
        if (strtolower((string) $this->email) === 'superadmin@gmail.com') {
            return true;
        }
        if (strtolower((string) $this->name) === 'super admin') {
            return true;
        }
        return false;
    }

    /**
     * Module Permissions relationship
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Check if user has specific permission on a module.
     * Actions: 'create', 'read', 'update', 'delete', 'print'
     */
    public function hasPermission(string $module, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Action normalization
        $action = strtolower(trim($action));
        $module = strtolower(trim($module));

        // In case permissions relationship was not eager loaded or is collection
        $permission = $this->relationLoaded('permissions')
            ? $this->permissions->firstWhere('module', $module)
            : $this->permissions()->where('module', $module)->first();

        if (!$permission) {
            return false;
        }

        return match ($action) {
            'create' => (bool) $permission->can_create,
            'read'   => (bool) $permission->can_read,
            'update' => (bool) $permission->can_update,
            'delete' => (bool) $permission->can_delete,
            'print'  => (bool) $permission->can_print,
            default  => false,
        };
    }

    /**
     * Check if user can access the module at all (if at least one action is granted)
     * If all are false/null, module is completely hidden.
     */
    public function canAccessModule(string $module): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $module = strtolower(trim($module));

        $permission = $this->relationLoaded('permissions')
            ? $this->permissions->firstWhere('module', $module)
            : $this->permissions()->where('module', $module)->first();

        if (!$permission) {
            return false;
        }

        return (bool) (
            $permission->can_read ||
            $permission->can_create ||
            $permission->can_update ||
            $permission->can_delete ||
            $permission->can_print
        );
    }

    /**
     * Resolve the first permitted route name for this user.
     */
    public function getFirstPermittedRoute(): string
    {
        if ($this->isSuperAdmin() || $this->hasPermission('dashboard', 'read')) {
            return 'dashboard';
        }

        $priorityModules = [
            'orders'    => 'orders.index',
            'sales'     => 'sales.index',
            'receipts'  => 'receipts.index',
            'returns'   => 'returns.index',
            'products'  => 'products.index',
            'customers' => 'customers.index',
            'barcode'   => 'barcode.index',
            'settings'  => 'settings.company',
        ];

        foreach ($priorityModules as $module => $routeName) {
            if ($this->canAccessModule($module)) {
                return $routeName;
            }
        }

        return 'login';
    }

    /**
     * User's Sale Orders
     */
    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class);
    }

    /**
     * User's Sales Invoices
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * User's Cash Receipts
     */
    public function cashReceipts(): HasMany
    {
        return $this->hasMany(CashReceipt::class);
    }

    /**
     * User's Returns
     */
    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }

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
            'is_active' => 'boolean',
        ];
    }
}
