<?php

namespace Tests\Feature;

use App\Models\BarcodeConfiguration;
use App\Models\Category;
use App\Models\CashReceipt;
use App\Models\Customer;
use App\Models\Dozen;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleOrder;
use App\Models\Sku;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $staffUser;
    protected User $otherStaffUser;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('waqar$1234'), 'role' => 'superadmin', 'is_active' => true]
        );
        $this->superAdmin->role = 'superadmin';
        $this->superAdmin->is_active = true;
        $this->superAdmin->save();

        $this->staffUser = User::create([
            'name' => 'Staff Member Alpha',
            'phone1' => '03001234567',
            'phone2' => '03001234568',
            'phone3' => '03001234569',
            'ic_card' => '35201-1234567-1',
            'address' => 'Shop 12, Market Road, Lahore',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->otherStaffUser = User::create([
            'name' => 'Staff Member Beta',
            'phone1' => '03007654321',
            'phone2' => '03007654322',
            'phone3' => '03007654323',
            'ic_card' => '35201-7654321-2',
            'address' => 'Shop 55, Plaza, Karachi',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->customer = Customer::firstOrCreate(
            ['name' => 'Al-Madina Garments'],
            [
                'code' => 888,
                'owner_name' => 'Haji Iqbal',
                'phone' => '03005556677',
                'city' => 'Lahore',
            ]
        );

        $category = Category::firstOrCreate(['name' => 'Polo Shirts']);
        $dozen = Dozen::firstOrCreate(['quantity' => 12], ['label' => '12 pieces']);
        $rule = PricingRule::firstOrCreate(['percentage' => 0], ['name' => 'Default 0%']);

        $sku = Sku::allocate();

        $this->product = Product::create([
            'sku_id' => $sku->id,
            'sku_code' => $sku->sku_code,
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $rule->id,
            'parent_category' => 'Bee Comfort',
            'product_code' => 'POLO-01',
            'sub_category' => 'Polo',
            'color' => 'Navy',
            'size' => 'L',
            'stock_quantity' => 100,
            'actual_price' => 500.00,
            'selling_price' => 700.00,
        ]);
    }

    /**
     * 1. Only Super Admin can access User Management pages.
     */
    public function test_only_super_admin_can_access_user_management(): void
    {
        // Staff user gets 403
        $this->actingAs($this->staffUser);
        $this->get(route('users.index'))->assertStatus(403);
        $this->get(route('users.create'))->assertStatus(403);

        // Super Admin gets 200
        $this->actingAs($this->superAdmin);
        $responseSuper = $this->get(route('users.index'));
        $responseSuper->assertStatus(200);
        $responseSuper->assertSee('User Management &amp; Permissions', false);
        $responseSuper->assertSee('Staff Member Alpha');
    }

    /**
     * 2. Super Admin can create user with IC, 3 phones, address, password, and permissions.
     */
    public function test_super_admin_can_create_user_with_ic_three_phones_and_permissions(): void
    {
        $this->actingAs($this->superAdmin);

        $userData = [
            'name' => 'Ali Raza Staff',
            'ic_card' => '35202-9876543-1',
            'phone1' => '03119876543',
            'phone2' => '03229876543',
            'phone3' => '03339876543',
            'address' => 'House 45, Street 2, Faisalabad',
            'password' => 'pass1234',
            'is_active' => 1,
            'permissions' => [
                'orders' => ['read' => 1, 'create' => 1, 'update' => 1, 'print' => 1],
                'sales' => ['read' => 1, 'create' => 1],
            ],
        ];

        $response = $this->post(route('users.store'), $userData);
        $response->assertRedirect(route('users.index'));

        $newUser = User::where('phone1', '03119876543')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Ali Raza Staff', $newUser->name);
        $this->assertEquals('35202-9876543-1', $newUser->ic_card);
        $this->assertEquals('03229876543', $newUser->phone2);
        $this->assertEquals('03339876543', $newUser->phone3);
        $this->assertTrue(Hash::check('pass1234', $newUser->password));

        // Check permissions saved
        $this->assertTrue($newUser->hasPermission('orders', 'read'));
        $this->assertTrue($newUser->hasPermission('orders', 'create'));
        $this->assertTrue($newUser->hasPermission('orders', 'update'));
        $this->assertTrue($newUser->hasPermission('orders', 'print'));
        $this->assertFalse($newUser->hasPermission('orders', 'delete'));

        $this->assertTrue($newUser->hasPermission('sales', 'read'));
        $this->assertTrue($newUser->hasPermission('sales', 'create'));
        $this->assertFalse($newUser->hasPermission('sales', 'update'));
        $this->assertFalse($newUser->hasPermission('sales', 'delete'));

        // Products completely null
        $this->assertFalse($newUser->canAccessModule('products'));
        $this->assertFalse($newUser->hasPermission('products', 'read'));
    }

    /**
     * 3. Super Admin can update user details and modify the permissions matrix.
     */
    public function test_super_admin_can_update_user_and_permissions_matrix(): void
    {
        $this->actingAs($this->superAdmin);

        // Update profile
        $response = $this->put(route('users.update', $this->staffUser), [
            'name' => 'Staff Alpha Updated',
            'phone1' => '03001234567',
            'phone2' => '03009999999',
            'phone3' => '03008888888',
            'ic_card' => '35201-9999999-9',
            'address' => 'Updated Address in Lahore',
            'is_active' => 1,
        ]);
        $response->assertRedirect(route('users.index'));

        $this->staffUser->refresh();
        $this->assertEquals('Staff Alpha Updated', $this->staffUser->name);
        $this->assertEquals('03009999999', $this->staffUser->phone2);

        // Update permissions matrix
        $permData = [
            'permissions' => [
                'receipts' => ['read' => 1, 'create' => 1, 'print' => 1],
                'customers' => ['read' => 1],
            ],
        ];

        $responsePerm = $this->put(route('users.permissions.update', $this->staffUser), $permData);
        $responsePerm->assertRedirect(route('users.index'));

        $this->staffUser->refresh();
        $this->assertTrue($this->staffUser->hasPermission('receipts', 'read'));
        $this->assertTrue($this->staffUser->hasPermission('receipts', 'create'));
        $this->assertTrue($this->staffUser->hasPermission('receipts', 'print'));
        $this->assertFalse($this->staffUser->hasPermission('receipts', 'delete'));
        $this->assertTrue($this->staffUser->hasPermission('customers', 'read'));
        $this->assertFalse($this->staffUser->hasPermission('customers', 'create'));
        $this->assertFalse($this->staffUser->canAccessModule('orders'));
    }

    /**
     * 4. Super Admin can toggle user active/deactive status.
     */
    public function test_super_admin_can_toggle_user_status(): void
    {
        $this->actingAs($this->superAdmin);

        // Toggle to deactivated
        $response = $this->patch(route('users.toggle-status', $this->staffUser));
        $response->assertSessionHas('success');

        $this->staffUser->refresh();
        $this->assertFalse((bool) $this->staffUser->is_active);

        // Toggle back to activated
        $response2 = $this->patch(route('users.toggle-status', $this->staffUser));
        $response2->assertSessionHas('success');

        $this->staffUser->refresh();
        $this->assertTrue((bool) $this->staffUser->is_active);
    }

    /**
     * 5. User logs in with Phone 1 and password; deactivated user login is blocked.
     */
    public function test_user_authentication_with_phone1_and_deactivation_check(): void
    {
        // Successful login
        $response = $this->post(route('login.post'), [
            'login' => '03001234567',
            'password' => 'secret123',
        ]);
        $this->assertAuthenticatedAs($this->staffUser);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Deactivate user
        $this->staffUser->update(['is_active' => false]);

        $responseDeactivated = $this->post(route('login.post'), [
            'login' => '03001234567',
            'password' => 'secret123',
        ]);
        $this->assertGuest();
        $responseDeactivated->assertSessionHasErrors(['login']);
    }

    /**
     * 6. Dynamic Sidebar navigation: shows only permitted modules and hides null/unchecked modules.
     */
    public function test_sidebar_dynamically_shows_permitted_modules_and_hides_null_modules(): void
    {
        // Grant ONLY orders and receipts permissions
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'orders',
            'can_read' => true,
        ]);
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'receipts',
            'can_read' => true,
        ]);

        $this->actingAs($this->staffUser);
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);

        // Visible in navigation
        $response->assertSee('Sale Orders');
        $response->assertSee('Cash Receipts (CRV)');

        // Hiddens: products, sales, returns, customers, barcode, settings, users
        $response->assertDontSee('Products & Stock', false);
        $response->assertDontSee('Sales Invoices');
        $response->assertDontSee('Bill Returns');
        $response->assertDontSee('Customers & Ledger', false);
        $response->assertDontSee('Print Barcode Roll');
        $response->assertDontSee('Settings & Config', false);
        $response->assertDontSee('Users & Permissions', false);

        // Super Admin sees everything
        $this->actingAs($this->superAdmin);
        $responseSuper = $this->get(route('orders.index'));
        $responseSuper->assertSee('Products & Stock', false);
        $responseSuper->assertSee('Sales Invoices');
        $responseSuper->assertSee('Bill Returns');
        $responseSuper->assertSee('Cash Receipts (CRV)');
        $responseSuper->assertSee('Customers & Ledger', false);
        $responseSuper->assertSee('Print Barcode Roll');
        $responseSuper->assertSee('Settings & Config', false);
        $responseSuper->assertSee('Users & Permissions', false);
    }

    /**
     * 7. Granular Route Permission enforcement (403 on unpermitted actions).
     */
    public function test_granular_permission_route_enforcement(): void
    {
        // Give staffUser only orders:read (CANNOT create)
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'orders',
            'can_read' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_print' => false,
        ]);

        $this->actingAs($this->staffUser);

        // Read index: allowed
        $this->get(route('orders.index'))->assertStatus(200);

        // Create page & post: blocked with 403
        $this->get(route('orders.create'))->assertStatus(403);
        $this->post(route('orders.store'), [])->assertStatus(403);

        // Blocked unpermitted modules
        $this->get(route('products.index'))->assertStatus(403);
        $this->get(route('barcode.index'))->assertStatus(403);
        $this->get(route('pricing.index'))->assertStatus(403);
        $this->get(route('settings.company'))->assertStatus(403);
    }

    /**
     * 8. Action buttons in views only appear when permission is granted.
     */
    public function test_action_buttons_render_only_when_permission_granted(): void
    {
        // Case A: Read only (no create, no print, no delete)
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'orders',
            'can_read' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_print' => false,
        ]);

        $order = SaleOrder::create([
            'user_id' => $this->staffUser->id,
            'customer_id' => $this->customer->id,
            'order_no' => 'ORD-BTN-001',
            'status' => 'booked',
            'order_date' => now()->toDateString(),
            'total_quantity' => 5,
        ]);

        $this->actingAs($this->staffUser);
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);

        // "Book New Order" button should NOT be rendered
        $response->assertDontSee('Book New Order');
        // Edit, View, and Delete buttons should NOT be rendered
        $response->assertDontSee('✏️ Edit');
        $response->assertDontSee('👁️ View');
        $response->assertDontSee('🗑️ Delete');

        // Case B: Grant create and print
        $this->staffUser->permissions()->where('module', 'orders')->update([
            'can_create' => true,
            'can_print' => true,
        ]);

        $this->staffUser->refresh();
        $responseWithPerm = $this->get(route('orders.index'));
        $responseWithPerm->assertStatus(200);
        $responseWithPerm->assertSee('Book New Order');
        $responseWithPerm->assertSee('👁️ View');
    }

    /**
     * 9. Sale Orders Scoping: Staff A vs Staff B isolation; Super Admin sees both + filter by user.
     */
    public function test_sale_orders_ownership_scoping(): void
    {
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'orders',
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'can_print' => true,
        ]);

        UserPermission::create([
            'user_id' => $this->otherStaffUser->id,
            'module' => 'orders',
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'can_print' => true,
        ]);

        $orderAlpha = SaleOrder::create([
            'user_id' => $this->staffUser->id,
            'customer_id' => $this->customer->id,
            'order_no' => 'ORD-ALPHA-101',
            'status' => 'booked',
            'order_date' => now()->toDateString(),
            'total_quantity' => 10,
        ]);

        $orderBeta = SaleOrder::create([
            'user_id' => $this->otherStaffUser->id,
            'customer_id' => $this->customer->id,
            'order_no' => 'ORD-BETA-202',
            'status' => 'booked',
            'order_date' => now()->toDateString(),
            'total_quantity' => 20,
        ]);

        // Staff Alpha sees only Alpha's order
        $this->actingAs($this->staffUser);
        $responseAlpha = $this->get(route('orders.index'));
        $responseAlpha->assertStatus(200);
        $responseAlpha->assertSee('ORD-ALPHA-101');
        $responseAlpha->assertDontSee('ORD-BETA-202');

        // Cannot show, edit or print Beta's order
        $this->get(route('orders.show', $orderBeta))->assertStatus(403);
        $this->get(route('orders.edit', $orderBeta))->assertStatus(403);
        $this->get(route('orders.print', $orderBeta))->assertStatus(403);

        // Can show and print own order
        $this->get(route('orders.show', $orderAlpha))->assertStatus(200);
        $this->get(route('orders.print', $orderAlpha))->assertStatus(200);

        // Super Admin sees both + user badges + filter dropdown
        $this->actingAs($this->superAdmin);
        $responseSuper = $this->get(route('orders.index'));
        $responseSuper->assertStatus(200);
        $responseSuper->assertSee('ORD-ALPHA-101');
        $responseSuper->assertSee('ORD-BETA-202');
        $responseSuper->assertSee('Filter by User');

        // Super Admin filters by Alpha
        $responseFilter = $this->get(route('orders.index', ['user_id' => $this->staffUser->id]));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('ORD-ALPHA-101');
        $responseFilter->assertDontSee('ORD-BETA-202');
    }

    /**
     * 10. Sales Invoices Scoping: Staff A vs Staff B isolation; Super Admin sees both + filter by user.
     */
    public function test_sales_invoices_ownership_scoping(): void
    {
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'sales',
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_print' => true,
        ]);

        UserPermission::create([
            'user_id' => $this->otherStaffUser->id,
            'module' => 'sales',
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_print' => true,
        ]);

        $saleAlpha = Sale::create([
            'user_id' => $this->staffUser->id,
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'SIN-ALPHA-01',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => 7000.00,
            'paid_amount' => 7000.00,
            'balance_due' => 0.00,
        ]);

        $saleBeta = Sale::create([
            'user_id' => $this->otherStaffUser->id,
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'SIN-BETA-02',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => 14000.00,
            'paid_amount' => 14000.00,
            'balance_due' => 0.00,
        ]);

        // Staff Alpha sees only SIN-ALPHA-01
        $this->actingAs($this->staffUser);
        $responseAlpha = $this->get(route('sales.index'));
        $responseAlpha->assertStatus(200);
        $responseAlpha->assertSee('SIN-ALPHA-01');
        $responseAlpha->assertDontSee('SIN-BETA-02');

        // Cannot view, edit or print Beta's sale
        $this->get(route('sales.show', $saleBeta))->assertStatus(403);
        $this->get(route('sales.edit', $saleBeta))->assertStatus(403);
        $this->get(route('sales.print', $saleBeta))->assertStatus(403);

        // Can view and print own sale
        $this->get(route('sales.show', $saleAlpha))->assertStatus(200);
        $this->get(route('sales.print', $saleAlpha))->assertStatus(200);

        // Super Admin sees both
        $this->actingAs($this->superAdmin);
        $responseSuper = $this->get(route('sales.index'));
        $responseSuper->assertStatus(200);
        $responseSuper->assertSee('SIN-ALPHA-01');
        $responseSuper->assertSee('SIN-BETA-02');
        $responseSuper->assertSee('All Users...');

        // Super Admin filter
        $responseFilter = $this->get(route('sales.index', ['user_id' => $this->staffUser->id]));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('SIN-ALPHA-01');
        $responseFilter->assertDontSee('SIN-BETA-02');
    }

    /**
     * 11. Cash Receipts (CRV) Scoping: Staff A vs Staff B isolation; Super Admin sees both + filter by user.
     */
    public function test_cash_receipts_ownership_scoping(): void
    {
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'receipts',
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'can_print' => true,
        ]);

        UserPermission::create([
            'user_id' => $this->otherStaffUser->id,
            'module' => 'receipts',
            'can_read' => true,
            'can_create' => true,
        ]);

        $receiptAlpha = CashReceipt::create([
            'user_id' => $this->staffUser->id,
            'voucher_no' => 'CRV-ALPHA-501',
            'customer_id' => $this->customer->id,
            'payment_date' => now()->toDateString(),
            'amount' => 5000,
        ]);

        $receiptBeta = CashReceipt::create([
            'user_id' => $this->otherStaffUser->id,
            'voucher_no' => 'CRV-BETA-602',
            'customer_id' => $this->customer->id,
            'payment_date' => now()->toDateString(),
            'amount' => 12000,
        ]);

        // Staff Alpha view
        $this->actingAs($this->staffUser);
        $response = $this->get(route('receipts.index'));
        $response->assertStatus(200);
        $response->assertSee('CRV-ALPHA-501');
        $response->assertDontSee('CRV-BETA-602');

        // Cannot view or edit Beta's receipt
        $this->get(route('receipts.show', $receiptBeta))->assertStatus(403);
        $this->get(route('receipts.edit', $receiptBeta))->assertStatus(403);

        // Super Admin sees both
        $this->actingAs($this->superAdmin);
        $responseSuper = $this->get(route('receipts.index'));
        $responseSuper->assertStatus(200);
        $responseSuper->assertSee('CRV-ALPHA-501');
        $responseSuper->assertSee('CRV-BETA-602');
        $responseSuper->assertSee('All Users...');

        // Super Admin filter
        $responseFilter = $this->get(route('receipts.index', ['user_id' => $this->otherStaffUser->id]));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('CRV-BETA-602');
        $responseFilter->assertDontSee('CRV-ALPHA-501');
    }

    /**
     * 12. Bill Returns Scoping: Staff A vs Staff B isolation; Super Admin sees both + filter by user.
     */
    public function test_bill_returns_ownership_scoping(): void
    {
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'returns',
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_print' => true,
        ]);

        UserPermission::create([
            'user_id' => $this->otherStaffUser->id,
            'module' => 'returns',
            'can_read' => true,
            'can_create' => true,
        ]);

        $returnAlpha = ReturnRecord::create([
            'user_id' => $this->staffUser->id,
            'bill_type' => 'bee_comfort',
            'return_no' => 'RET-ALPHA-01',
            'customer_id' => $this->customer->id,
            'return_date' => now()->toDateString(),
            'total_amount' => 1400.00,
        ]);

        $returnBeta = ReturnRecord::create([
            'user_id' => $this->otherStaffUser->id,
            'bill_type' => 'bee_comfort',
            'return_no' => 'RET-BETA-02',
            'customer_id' => $this->customer->id,
            'return_date' => now()->toDateString(),
            'total_amount' => 2800.00,
        ]);

        // Staff Alpha view
        $this->actingAs($this->staffUser);
        $response = $this->get(route('returns.index'));
        $response->assertStatus(200);
        $response->assertSee('RET-ALPHA-01');
        $response->assertDontSee('RET-BETA-02');

        // Cannot view or edit Beta's return
        $this->get(route('returns.show', $returnBeta))->assertStatus(403);
        $this->get(route('returns.edit', $returnBeta))->assertStatus(403);

        // Super Admin sees both
        $this->actingAs($this->superAdmin);
        $responseSuper = $this->get(route('returns.index'));
        $responseSuper->assertStatus(200);
        $responseSuper->assertSee('RET-ALPHA-01');
        $responseSuper->assertSee('RET-BETA-02');
        $responseSuper->assertSee('All Users...');

        // Super Admin filter
        $responseFilter = $this->get(route('returns.index', ['user_id' => $this->staffUser->id]));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('RET-ALPHA-01');
        $responseFilter->assertDontSee('RET-BETA-02');
    }

    /**
     * 13. Dashboard Scoping: User with dashboard read sees only their own metrics; without it redirects.
     */
    public function test_dashboard_user_scoping_and_redirect_fallback(): void
    {
        // Case A: Staff without dashboard permission visiting /dashboard redirects to first permitted module
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'orders',
            'can_read' => true,
        ]);

        $this->actingAs($this->staffUser);
        $responseRedirect = $this->get(route('dashboard'));
        $responseRedirect->assertRedirect(route('orders.index'));

        // Case B: Grant dashboard read permission
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'dashboard',
            'can_read' => true,
        ]);

        // Create sale for Alpha (5,000) and Beta (50,000)
        Sale::create([
            'user_id' => $this->staffUser->id,
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'SIN-DASH-ALPHA',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => 5000.00,
            'paid_amount' => 5000.00,
            'balance_due' => 0.00,
        ]);

        Sale::create([
            'user_id' => $this->otherStaffUser->id,
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'SIN-DASH-BETA',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => 50000.00,
            'paid_amount' => 50000.00,
            'balance_due' => 0.00,
        ]);

        $this->actingAs($this->staffUser);
        $responseDash = $this->get(route('dashboard'));
        $responseDash->assertStatus(200);
        // Alpha sees their invoice
        $responseDash->assertSee('SIN-DASH-ALPHA');
        // Alpha does NOT see Beta's invoice
        $responseDash->assertDontSee('SIN-DASH-BETA');
    }

    /**
     * 14. Super Admin can delete a staff user, but cannot delete the Super Admin account.
     */
    public function test_super_admin_can_delete_staff_user_safely(): void
    {
        $this->actingAs($this->superAdmin);

        // Delete staff user
        $response = $this->delete(route('users.destroy', $this->otherStaffUser));
        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $this->otherStaffUser->id]);

        // Super Admin self delete rejected
        $responseSuper = $this->delete(route('users.destroy', $this->superAdmin));
        $responseSuper->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }
}
