<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Standard list of all system modules and supported actions.
     */
    public static function getSystemModules(): array
    {
        return [
            'dashboard' => [
                'name' => 'Dashboard Overview',
                'icon' => 'bi-speedometer2',
                'description' => 'Main analytical dashboard, statistics and depleted stock notifications',
                'actions' => ['read'],
            ],
            'products' => [
                'name' => 'Products & Stock',
                'icon' => 'bi-boxes',
                'description' => 'Product registration, SKU allocations, inventory levels and stock adjustments',
                'actions' => ['create', 'read', 'update', 'delete'],
            ],
            'orders' => [
                'name' => 'Sale Orders (Matrix Booking)',
                'icon' => 'bi-journal-check',
                'description' => 'Size matrix order bookings, voucher tracking and printings',
                'actions' => ['create', 'read', 'update', 'delete', 'print'],
            ],
            'sales' => [
                'name' => 'Sales Invoices',
                'icon' => 'bi-receipt',
                'description' => 'Customer sales invoices, Bee Comfort & China bills, A4 invoice printing',
                'actions' => ['create', 'read', 'update', 'delete', 'print'],
            ],
            'returns' => [
                'name' => 'Bill Returns',
                'icon' => 'bi-arrow-counterclockwise',
                'description' => 'Customer return records, invoice reversals, restock movements',
                'actions' => ['create', 'read', 'update', 'delete', 'print'],
            ],
            'receipts' => [
                'name' => 'Cash Receipts Voucher (CRV)',
                'icon' => 'bi-cash-coin',
                'description' => 'Customer payment vouchers, cash collection accounting and ledger credits',
                'actions' => ['create', 'read', 'update', 'delete', 'print'],
            ],
            'customers' => [
                'name' => 'Customers & Ledgers',
                'icon' => 'bi-people',
                'description' => 'Customer master directories, ledger histories and account statements',
                'actions' => ['create', 'read', 'update', 'delete', 'print'],
            ],
            'barcode' => [
                'name' => 'Barcode Roll Printing',
                'icon' => 'bi-printer',
                'description' => 'Thermal 32x22mm roll barcode generation and batch printing',
                'actions' => ['read', 'print'],
            ],
            'settings' => [
                'name' => 'Settings & Configuration',
                'icon' => 'bi-gear',
                'description' => 'Company profile, pricing markup rules, categories, dozens and thermal setups',
                'actions' => ['read', 'update'],
            ],
        ];
    }

    /**
     * Display listing of all registered users.
     */
    public function index(Request $request): View
    {
        $query = User::with('permissions')->orderBy('id', 'desc');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone1', 'like', "%{$search}%")
                  ->orWhere('phone2', 'like', "%{$search}%")
                  ->orWhere('phone3', 'like', "%{$search}%")
                  ->orWhere('ic_card', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        return view('users.index', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    /**
     * Show the form for creating a new user with permissions.
     */
    public function create(): View
    {
        $modules = static::getSystemModules();
        return view('users.create', compact('modules'));
    }

    /**
     * Store a newly created user along with their assigned module permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'ic_card'   => ['nullable', 'string', 'max:50'],
            'phone1'    => ['required', 'string', 'max:30', 'unique:users,phone1'],
            'phone2'    => ['nullable', 'string', 'max:30'],
            'phone3'    => ['nullable', 'string', 'max:30'],
            'address'   => ['nullable', 'string', 'max:1000'],
            'password'  => ['required', 'string', 'min:4'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'ic_card'   => $validated['ic_card'] ?? null,
            'phone1'    => $validated['phone1'],
            'phone2'    => $validated['phone2'] ?? null,
            'phone3'    => $validated['phone3'] ?? null,
            'address'   => $validated['address'] ?? null,
            'password'  => Hash::make($validated['password']),
            'role'      => 'user',
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Save module permissions
        $modules = static::getSystemModules();
        $submittedPerms = $request->input('permissions', []);

        foreach ($modules as $modKey => $modInfo) {
            $permData = $submittedPerms[$modKey] ?? [];

            $canCreate = !empty($permData['create']);
            $canRead   = !empty($permData['read']);
            $canUpdate = !empty($permData['update']);
            $canDelete = !empty($permData['delete']);
            $canPrint  = !empty($permData['print']);

            UserPermission::create([
                'user_id'    => $user->id,
                'module'     => $modKey,
                'can_create' => $canCreate,
                'can_read'   => $canRead,
                'can_update' => $canUpdate,
                'can_delete' => $canDelete,
                'can_print'  => $canPrint,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' created successfully with specified permissions.");
    }

    /**
     * Show form for editing an existing user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update user details and optionally reset password.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'ic_card'   => ['nullable', 'string', 'max:50'],
            'phone1'    => ['required', 'string', 'max:30', Rule::unique('users', 'phone1')->ignore($user->id)],
            'phone2'    => ['nullable', 'string', 'max:30'],
            'phone3'    => ['nullable', 'string', 'max:30'],
            'address'   => ['nullable', 'string', 'max:1000'],
            'password'  => ['nullable', 'string', 'min:4'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'ic_card'   => $validated['ic_card'] ?? null,
            'phone1'    => $validated['phone1'],
            'phone2'    => $validated['phone2'] ?? null,
            'phone3'    => $validated['phone3'] ?? null,
            'address'   => $validated['address'] ?? null,
            'is_active' => $user->isSuperAdmin() ? true : $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' details updated successfully.");
    }

    /**
     * Show dedicated permissions matrix page for a specific user.
     */
    public function editPermissions(User $user): View
    {
        $modules = static::getSystemModules();
        $userPermissions = $user->permissions->keyBy('module');

        return view('users.permissions', compact('user', 'modules', 'userPermissions'));
    }

    /**
     * Update/sync permissions for a user.
     */
    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $modules = static::getSystemModules();
        $submittedPerms = $request->input('permissions', []);

        foreach ($modules as $modKey => $modInfo) {
            $permData = $submittedPerms[$modKey] ?? [];

            $canCreate = !empty($permData['create']);
            $canRead   = !empty($permData['read']);
            $canUpdate = !empty($permData['update']);
            $canDelete = !empty($permData['delete']);
            $canPrint  = !empty($permData['print']);

            UserPermission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module'  => $modKey,
                ],
                [
                    'can_create' => $canCreate,
                    'can_read'   => $canRead,
                    'can_update' => $canUpdate,
                    'can_delete' => $canDelete,
                    'can_print'  => $canPrint,
                ]
            );
        }

        return redirect()->route('users.index')
            ->with('success', "Permissions updated successfully for '{$user->name}'.");
    }

    /**
     * Toggle active/inactive status of a user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin account status cannot be deactivated.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User '{$user->name}' has been {$statusStr}.");
    }

    /**
     * Remove a user from the system.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot delete the Super Admin user account.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User '{$userName}' deleted successfully.");
    }
}
