<?php

namespace Tests\Feature;

use App\Models\CashReceipt;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserPermission;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashReceiptAndCustomerBarcodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $staffUser;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // 1. Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Administrator', 'password' => bcrypt('waqar$1234'), 'role' => 'superadmin', 'is_active' => true]
        );
        $this->superAdmin->role = 'superadmin';
        $this->superAdmin->is_active = true;
        $this->superAdmin->save();

        // 2. Staff User
        $this->staffUser = User::create([
            'name' => 'Field Salesman',
            'phone1' => '03007654321',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // 3. Sample Customer
        $this->customer = Customer::create([
            'name' => 'Al-Madina Fabrics',
            'code' => 'B078',
            'owner_name' => 'Haji Muhammad',
            'phone' => '03211112233',
            'city' => 'Faisalabad',
            'address' => 'Clock Tower Bazaar',
        ]);
    }

    /**
     * 1. Customer Barcode Generator View & Print Tests
     */
    public function test_customer_barcode_generator_view_and_print(): void
    {
        $this->actingAs($this->superAdmin);

        // View Customer Barcode Studio
        $response = $this->get(route('barcode.customer'));
        $response->assertStatus(200);
        $response->assertSee('Customer Barcode Generator');
        $response->assertSee('Al-Madina Fabrics');
        $response->assertSee('B078');

        // Print Customer 32x22mm Thermal Barcode
        $printResponse = $this->post(route('barcode.customer.print'), [
            'customer_id' => $this->customer->id,
            'quantity' => 3,
        ]);

        $printResponse->assertStatus(200);
        $printResponse->assertSee('Al-Madina Fabrics');
        $printResponse->assertSee('B078');
        $printResponse->assertSee('data-code="B078"', false);
        $printResponse->assertSee('32mm 22mm', false);
    }

    /**
     * 2. CRV Payment Methods: EasyPaisa & JazzCash
     */
    public function test_crv_supports_easypaisa_and_jazzcash_payment_methods(): void
    {
        $this->actingAs($this->superAdmin);

        // Store with EasyPaisa
        $responseEasy = $this->post(route('receipts.store'), [
            'customer_id' => $this->customer->id,
            'voucher_no' => 'CRV-EP-001',
            'payment_date' => Carbon::today()->toDateString(),
            'amount' => 15000.00,
            'payment_method' => 'easypaisa',
            'notes' => 'Received via EasyPaisa Merchant Till',
        ]);

        $responseEasy->assertRedirect(route('receipts.index'));
        $this->assertDatabaseHas('cash_receipts', [
            'voucher_no' => 'CRV-EP-001',
            'payment_method' => 'easypaisa',
            'amount' => 15000.00,
        ]);

        // Store with JazzCash
        $responseJazz = $this->post(route('receipts.store'), [
            'customer_id' => $this->customer->id,
            'voucher_no' => 'CRV-JC-002',
            'payment_date' => Carbon::today()->toDateString(),
            'amount' => 25000.00,
            'payment_method' => 'jazzcash',
            'notes' => 'Received via JazzCash Account',
        ]);

        $responseJazz->assertRedirect(route('receipts.index'));
        $this->assertDatabaseHas('cash_receipts', [
            'voucher_no' => 'CRV-JC-002',
            'payment_method' => 'jazzcash',
            'amount' => 25000.00,
        ]);

        // Verify index displays badges for EasyPaisa and JazzCash
        $indexResponse = $this->get(route('receipts.index'));
        $indexResponse->assertSee('EasyPaisa');
        $indexResponse->assertSee('JazzCash');
    }

    /**
     * 3. CRV Single-Day Default Filtering (Today by default)
     */
    public function test_crv_defaults_to_today_filtering(): void
    {
        $this->actingAs($this->superAdmin);

        // Create a receipt for Today
        $todayReceipt = CashReceipt::create([
            'voucher_no' => 'CRV-TODAY-01',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'payment_date' => Carbon::today()->toDateString(),
            'amount' => 5000.00,
            'payment_method' => 'cash',
        ]);

        // Create a receipt from 5 days ago
        $oldReceipt = CashReceipt::create([
            'voucher_no' => 'CRV-OLD-05',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'payment_date' => Carbon::today()->subDays(5)->toDateString(),
            'amount' => 12000.00,
            'payment_method' => 'cash',
        ]);

        // 1. Default visit without query params: should only show today
        $response = $this->get(route('receipts.index'));
        $response->assertStatus(200);
        $response->assertSee('CRV-TODAY-01');
        $response->assertDontSee('CRV-OLD-05');
        $response->assertSee("Rs. 5,000.00");
        $response->assertDontSee("Rs. 17,000.00"); // Combined amount must not be shown on today's view

        // 2. View all dates history
        $responseAll = $this->get(route('receipts.index', ['all_dates' => 1]));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('CRV-TODAY-01');
        $responseAll->assertSee('CRV-OLD-05');
        $responseAll->assertSee("Rs. 17,000.00");

        // 3. Filter by older date specifically
        $responseOld = $this->get(route('receipts.index', [
            'from_date' => Carbon::today()->subDays(6)->toDateString(),
            'to_date' => Carbon::today()->subDays(4)->toDateString(),
        ]));
        $responseOld->assertStatus(200);
        $responseOld->assertDontSee('CRV-TODAY-01');
        $responseOld->assertSee('CRV-OLD-05');
        $responseOld->assertSee("Rs. 12,000.00");
    }

    /**
     * 4. Hide "Total Invoiced" Card for Non-Superadmin Users in CRV Form
     */
    public function test_total_invoiced_visibility_guard_in_crv_form(): void
    {
        // Give staffUser receipts permissions
        UserPermission::create([
            'user_id' => $this->staffUser->id,
            'module' => 'receipts',
            'can_create' => true,
            'can_read' => true,
        ]);

        // Super Admin sees "Total Invoiced"
        $this->actingAs($this->superAdmin);
        $superResponse = $this->get(route('receipts.create'));
        $superResponse->assertStatus(200);
        $superResponse->assertSee('Total Invoiced');
        $superResponse->assertSee('Previously Received');
        $superResponse->assertSee('Current Outstanding Due');

        // Staff User does NOT see "Total Invoiced"
        $this->actingAs($this->staffUser);
        $staffResponse = $this->get(route('receipts.create'));
        $staffResponse->assertStatus(200);
        $staffResponse->assertDontSee('Total Invoiced');
        $staffResponse->assertSee('Previously Received');
        $staffResponse->assertSee('Current Outstanding Due');
    }

    /**
     * 5. CRV 52mm Thermal Receipt Slip
     */
    public function test_crv_52mm_thermal_receipt_slip(): void
    {
        $this->actingAs($this->superAdmin);

        $receipt = CashReceipt::create([
            'voucher_no' => 'CRV-52MM-999',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'payment_date' => Carbon::today()->toDateString(),
            'amount' => 7500.00,
            'payment_method' => 'easypaisa',
            'notes' => '52mm Thermal Roll Test Slip',
        ]);

        $response = $this->get(route('receipts.print', $receipt));
        $response->assertStatus(200);
        $response->assertSee('52mm auto', false);
        $response->assertSee('CRV-52MM-999');
        $response->assertSee('Al-Madina Fabrics');
        $response->assertSee('B078');
        $response->assertSee('EasyPaisa');
        $response->assertSee('Rs. 7,500.00');
        $response->assertSee('CASH RECEIPT VOUCHER');
        $response->assertSee('Thank you for your business!');
    }
}
