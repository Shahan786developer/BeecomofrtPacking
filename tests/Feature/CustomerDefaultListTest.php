<?php

namespace Tests\Feature;

use App\Models\CashReceipt;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\UserPermission;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDefaultListTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Administrator', 'password' => bcrypt('waqar$1234'), 'role' => 'superadmin', 'is_active' => true]
        );
        $this->superAdmin->role = 'superadmin';
        $this->superAdmin->is_active = true;
        $this->superAdmin->save();

        $this->staffUser = User::create([
            'name' => 'Staff Salesman',
            'phone1' => '03009988776',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Test Customer registration and update with credit_days
     */
    public function test_customer_registration_and_update_with_credit_days(): void
    {
        $this->actingAs($this->superAdmin);

        // Store customer with 7 days term
        $response7 = $this->post(route('customers.store'), [
            'name' => 'Al-Rehman Cloth House',
            'owner_name' => 'Sheikh Rehman',
            'phone' => '03001112233',
            'city' => 'Faisalabad',
            'credit_days' => 7,
        ]);
        $response7->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Al-Rehman Cloth House',
            'credit_days' => 7,
        ]);

        $customer = Customer::where('name', 'Al-Rehman Cloth House')->first();
        $this->assertEquals('7 Days', $customer->credit_days_label);

        // Update to 15 days
        $response15 = $this->put(route('customers.update', $customer), [
            'name' => 'Al-Rehman Cloth House',
            'owner_name' => 'Sheikh Rehman',
            'phone' => '03001112233',
            'city' => 'Faisalabad',
            'credit_days' => 15,
        ]);
        $response15->assertRedirect(route('customers.index'));
        $customer->refresh();
        $this->assertEquals(15, $customer->credit_days);
        $this->assertEquals('15 Days', $customer->credit_days_label);

        // Update to null (None)
        $this->put(route('customers.update', $customer), [
            'name' => 'Al-Rehman Cloth House',
            'credit_days' => '',
        ]);
        $customer->refresh();
        $this->assertNull($customer->credit_days);
        $this->assertEquals('None', $customer->credit_days_label);
    }

    /**
     * 2. Test Customer Default List inclusion rules (FIFO Overdue logic)
     */
    public function test_customer_default_list_inclusion_and_tabs(): void
    {
        $this->actingAs($this->superAdmin);

        // Customer A: 7 days term, invoice from 5 days ago (NOT overdue)
        $custA = Customer::create([
            'name' => 'Cust A Not Overdue',
            'owner_name' => 'Owner A',
            'phone' => '03000000001',
            'city' => 'Lahore',
            'credit_days' => 7,
        ]);
        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $custA->id,
            'invoice_no' => 'BC-SIN-A1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(5)->toDateString(),
            'subtotal' => 10000,
            'discount' => 0,
            'total_amount' => 10000,
        ]);

        // Customer B: 7 days term, invoice from 8 days ago (OVERDUE by 1 day)
        $custB = Customer::create([
            'name' => 'Cust B Seven Day Overdue',
            'owner_name' => 'Owner B',
            'phone' => '03000000002',
            'city' => 'Faisalabad',
            'credit_days' => 7,
        ]);
        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $custB->id,
            'invoice_no' => 'BC-SIN-B1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(8)->toDateString(),
            'subtotal' => 25000,
            'discount' => 0,
            'total_amount' => 25000,
        ]);

        // Customer C: 15 days term, invoice from 16 days ago (OVERDUE by 1 day)
        $custC = Customer::create([
            'name' => 'Cust C Fifteen Day Overdue',
            'owner_name' => 'Owner C',
            'phone' => '03000000003',
            'city' => 'Karachi',
            'credit_days' => 15,
        ]);
        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $custC->id,
            'invoice_no' => 'BC-SIN-C1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(16)->toDateString(),
            'subtotal' => 50000,
            'discount' => 0,
            'total_amount' => 50000,
        ]);

        // Customer D: 30 days term, invoice from 35 days ago (OVERDUE by 5 days)
        $custD = Customer::create([
            'name' => 'Cust D Thirty Day Overdue',
            'owner_name' => 'Owner D',
            'phone' => '03000000004',
            'city' => 'Multan',
            'credit_days' => 30,
        ]);
        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $custD->id,
            'invoice_no' => 'BC-SIN-D1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(35)->toDateString(),
            'subtotal' => 80000,
            'discount' => 0,
            'total_amount' => 80000,
        ]);

        // Customer E: None (No term) with old invoice (NOT in default list)
        $custE = Customer::create([
            'name' => 'Cust E No Term',
            'owner_name' => 'Owner E',
            'phone' => '03000000005',
            'credit_days' => null,
        ]);
        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $custE->id,
            'invoice_no' => 'BC-SIN-E1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(40)->toDateString(),
            'subtotal' => 15000,
            'discount' => 0,
            'total_amount' => 15000,
        ]);

        // 1. Visit "All Defaulters" Tab
        $responseAll = $this->get(route('customers.default-list', ['tab' => 'all']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Customer Default List');
        $responseAll->assertSee('Cust B Seven Day Overdue');
        $responseAll->assertSee('Cust C Fifteen Day Overdue');
        $responseAll->assertSee('Cust D Thirty Day Overdue');
        $responseAll->assertDontSee('Cust A Not Overdue');
        $responseAll->assertDontSee('Cust E No Term');

        // Verify metrics
        $responseAll->assertSee('Rs. 155,000.00'); // 25,000 + 50,000 + 80,000

        // 2. Visit "7 Days" Tab
        $response7 = $this->get(route('customers.default-list', ['tab' => '7']));
        $response7->assertStatus(200);
        $response7->assertSee('Cust B Seven Day Overdue');
        $response7->assertDontSee('Cust C Fifteen Day Overdue');
        $response7->assertDontSee('Cust D Thirty Day Overdue');
        $response7->assertSee('Rs. 25,000.00');

        // 3. Visit "15 Days" Tab
        $response15 = $this->get(route('customers.default-list', ['tab' => '15']));
        $response15->assertStatus(200);
        $response15->assertSee('Cust C Fifteen Day Overdue');
        $response15->assertDontSee('Cust B Seven Day Overdue');
        $response15->assertDontSee('Cust D Thirty Day Overdue');
        $response15->assertSee('Rs. 50,000.00');

        // 4. Visit "30 Days" Tab
        $response30 = $this->get(route('customers.default-list', ['tab' => '30']));
        $response30->assertStatus(200);
        $response30->assertSee('Cust D Thirty Day Overdue');
        $response30->assertDontSee('Cust B Seven Day Overdue');
        $response30->assertDontSee('Cust C Fifteen Day Overdue');
        $response30->assertSee('Rs. 80,000.00');
    }

    /**
     * 3. Test that paying off previous balance removes customer from Default List
     */
    public function test_paying_previous_balance_removes_customer_from_default_list(): void
    {
        $this->actingAs($this->superAdmin);

        $customer = Customer::create([
            'name' => 'Recovery Target Shop',
            'owner_name' => 'Malik Tariq',
            'phone' => '03112233445',
            'city' => 'Rawalpindi',
            'credit_days' => 7,
        ]);

        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'BC-SIN-REC-1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(10)->toDateString(),
            'subtotal' => 30000,
            'discount' => 0,
            'total_amount' => 30000,
        ]);

        $this->assertTrue($customer->isDefaulter());

        // Check customer is listed on default list
        $resBefore = $this->get(route('customers.default-list'));
        $resBefore->assertSee('Recovery Target Shop');
        $resBefore->assertSee('Rs. 30,000.00');

        // Record full payment via Cash Receipt
        CashReceipt::create([
            'voucher_no' => 'CRV-REC-01',
            'customer_id' => $customer->id,
            'user_id' => $this->superAdmin->id,
            'payment_date' => Carbon::today()->toDateString(),
            'amount' => 30000,
            'payment_method' => 'cash',
        ]);

        $customer->refresh();
        $this->assertEquals(0, $customer->balance_due);
        $this->assertFalse($customer->isDefaulter());

        // Customer must no longer appear in default list
        $resAfter = $this->get(route('customers.default-list'));
        $resAfter->assertDontSee('Recovery Target Shop');
        $resAfter->assertSee('No Defaulter Customers Found!');
    }

    /**
     * 4. Test Search and Ledger Navigation
     */
    public function test_default_list_search_and_ledger_link(): void
    {
        $this->actingAs($this->superAdmin);

        $customer = Customer::create([
            'name' => 'Searchable Textile Mill',
            'owner_name' => 'Mian Babar',
            'phone' => '03337778899',
            'city' => 'Gujranwala',
            'credit_days' => 15,
        ]);

        Sale::create([
            'user_id' => $this->superAdmin->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'BC-SIN-SRCH-1',
            'bill_type' => 'bee_comfort',
            'invoice_date' => Carbon::today()->subDays(20)->toDateString(),
            'subtotal' => 45000,
            'discount' => 0,
            'total_amount' => 45000,
        ]);

        // Search matches
        $resFound = $this->get(route('customers.default-list', ['search' => 'Babar']));
        $resFound->assertStatus(200);
        $resFound->assertSee('Searchable Textile Mill');
        $resFound->assertSee('Mian Babar');
        $resFound->assertSee(route('customers.ledger', $customer));

        // Search misses
        $resNotFound = $this->get(route('customers.default-list', ['search' => 'NonExistentXYZ']));
        $resNotFound->assertStatus(200);
        $resNotFound->assertDontSee('Searchable Textile Mill');
    }
}
