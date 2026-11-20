<?php

namespace Tests\Feature;

use App\Models\CashReceipt;
use App\Models\Customer;
use App\Models\JournalVoucher;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalVoucherAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Customer $customer;

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

        $this->customer = Customer::create([
            'name' => 'Al-Madina Fabrics',
            'owner_name' => 'Haji Muhammad',
            'phone' => '03001234567',
            'credit_days' => 15,
        ]);
    }

    /**
     * Test Journal Voucher CRUD and customer balance impact
     */
    public function test_journal_voucher_crud_and_balance_calculation(): void
    {
        $this->actingAs($this->superAdmin);

        // 1. Next voucher number should be JV-001
        $this->assertEquals('JV-001', JournalVoucher::generateNextVoucherNo());

        // 2. Create a Debit JV (increases receivables / balance due)
        $response = $this->post(route('vouchers.store'), [
            'customer_id' => $this->customer->id,
            'type' => 'debit',
            'amount' => 5000,
            'voucher_date' => Carbon::today()->toDateString(),
            'reference_no' => 'REF-DEBIT-01',
            'notes' => 'Owner debit adjustment',
        ]);
        $response->assertRedirect(route('vouchers.index'));

        $this->assertDatabaseHas('journal_vouchers', [
            'voucher_no' => 'JV-001',
            'customer_id' => $this->customer->id,
            'type' => 'debit',
            'amount' => 5000,
        ]);

        $this->customer->refresh();
        $this->assertEquals(5000, $this->customer->balance_due);

        // 3. Create a Credit JV (decreases balance due / discount / owner adjustment)
        $responseCredit = $this->post(route('vouchers.store'), [
            'customer_id' => $this->customer->id,
            'type' => 'credit',
            'amount' => 1500,
            'voucher_date' => Carbon::today()->toDateString(),
            'reference_no' => 'REF-CREDIT-01',
            'notes' => 'Customer credit rebate',
        ]);
        $responseCredit->assertRedirect(route('vouchers.index'));

        $this->assertDatabaseHas('journal_vouchers', [
            'voucher_no' => 'JV-002',
            'customer_id' => $this->customer->id,
            'type' => 'credit',
            'amount' => 1500,
        ]);

        $this->customer->refresh();
        // 5000 debit - 1500 credit = 3500
        $this->assertEquals(3500, $this->customer->balance_due);

        // 4. Update the credit voucher
        $creditVoucher = JournalVoucher::where('voucher_no', 'JV-002')->first();
        $responseUpdate = $this->put(route('vouchers.update', $creditVoucher), [
            'customer_id' => $this->customer->id,
            'type' => 'credit',
            'amount' => 2000,
            'voucher_date' => Carbon::today()->toDateString(),
            'reference_no' => 'REF-CREDIT-MODIFIED',
            'notes' => 'Customer credit rebate modified',
        ]);
        $responseUpdate->assertRedirect(route('vouchers.index'));

        $this->customer->refresh();
        // 5000 - 2000 = 3000
        $this->assertEquals(3000, $this->customer->balance_due);

        // 5. Delete voucher
        $responseDelete = $this->delete(route('vouchers.destroy', $creditVoucher));
        $responseDelete->assertRedirect(route('vouchers.index'));
        $this->assertDatabaseMissing('journal_vouchers', ['id' => $creditVoucher->id]);

        $this->customer->refresh();
        $this->assertEquals(5000, $this->customer->balance_due);
    }

    /**
     * Test Customer Ledger includes JV and has completely removed Opening Balance
     */
    public function test_customer_ledger_opening_balance_removed_and_jv_present(): void
    {
        $this->actingAs($this->superAdmin);

        // Create Sale
        Sale::create([
            'invoice_no' => 'BC-1001',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'invoice_date' => Carbon::today()->subDays(2),
            'bill_type' => 'bee_comfort',
            'total_amount' => 12000,
            'previous_balance' => 0,
        ]);

        // Create Cash Receipt
        CashReceipt::create([
            'voucher_no' => 'CRV-1001',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'payment_date' => Carbon::today()->subDays(1),
            'amount' => 4000,
        ]);

        // Create JV Debit
        JournalVoucher::create([
            'voucher_no' => 'JV-001',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'voucher_date' => Carbon::today(),
            'type' => 'debit',
            'amount' => 1000,
            'notes' => 'Ledger Debit Test',
        ]);

        // View Customer Ledger
        $ledgerResponse = $this->get(route('customers.ledger', $this->customer));
        $ledgerResponse->assertOk();
        $ledgerResponse->assertSee('BC-1001');
        $ledgerResponse->assertSee('CRV-1001');
        $ledgerResponse->assertSee('JV-001');
        $ledgerResponse->assertSee('New JV Voucher');

        // Verify Opening Balance row is NOT present
        $ledgerResponse->assertDontSee('Balance Brought Forward');
        $ledgerResponse->assertDontSee('Opening Balance:');
    }

    /**
     * Test Customer Statement view has no 4 metric boxes and no opening balance
     */
    public function test_customer_statement_ink_friendly_view(): void
    {
        $this->actingAs($this->superAdmin);

        // Create JV
        JournalVoucher::create([
            'voucher_no' => 'JV-001',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'voucher_date' => Carbon::today(),
            'type' => 'credit',
            'amount' => 800,
        ]);

        $statementResponse = $this->get(route('customers.print-statement', $this->customer));
        $statementResponse->assertOk();

        // 4 summary boxes should be removed
        $statementResponse->assertDontSee('summary-cards-grid');
        $statementResponse->assertDontSee('Balance Brought Forward');

        // JV badge should be present
        $statementResponse->assertSee('JV VOUCHER');
        $statementResponse->assertSee('JV-001');
    }

    /**
     * Test Sales Index defaults to current month and includes PDF button
     */
    public function test_sales_index_default_month_and_pdf_button(): void
    {
        $this->actingAs($this->superAdmin);

        $now = Carbon::now();

        // Sale in current month
        $currentMonthSale = Sale::create([
            'invoice_no' => 'BC-CURR',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'invoice_date' => $now->copy()->startOfMonth()->addDays(2),
            'bill_type' => 'bee_comfort',
            'total_amount' => 5000,
        ]);

        // Sale in previous month
        $prevMonthSale = Sale::create([
            'invoice_no' => 'BC-PREV',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'invoice_date' => $now->copy()->subMonths(1)->startOfMonth()->addDays(2),
            'bill_type' => 'bee_comfort',
            'total_amount' => 8000,
        ]);

        // 1. Default request should only show current month sale
        $indexResponse = $this->get(route('sales.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('BC-CURR');
        $indexResponse->assertDontSee('BC-PREV');
        $indexResponse->assertSee('bi-file-earmark-pdf'); // PDF action button

        // 2. Request with all_dates=1 should show both
        $allResponse = $this->get(route('sales.index', ['all_dates' => 1]));
        $allResponse->assertOk();
        $allResponse->assertSee('BC-CURR');
        $allResponse->assertSee('BC-PREV');

        // 3. Search works for customer and invoice #
        $searchResponse = $this->get(route('sales.index', ['search' => 'Al-Madina', 'all_dates' => 1]));
        $searchResponse->assertOk();
        $searchResponse->assertSee('BC-CURR');
    }

    /**
     * Test Dashboard Period Filters
     */
    public function test_dashboard_period_filtering(): void
    {
        $this->actingAs($this->superAdmin);

        // Sale today
        Sale::create([
            'invoice_no' => 'BC-TODAY',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'invoice_date' => Carbon::today(),
            'bill_type' => 'bee_comfort',
            'total_amount' => 1000,
        ]);

        // Sale 5 days ago
        Sale::create([
            'invoice_no' => 'BC-5DAYS',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'invoice_date' => Carbon::today()->subDays(5),
            'bill_type' => 'bee_comfort',
            'total_amount' => 2000,
        ]);

        // Sale 2 months ago
        Sale::create([
            'invoice_no' => 'BC-2MONTHS',
            'customer_id' => $this->customer->id,
            'user_id' => $this->superAdmin->id,
            'invoice_date' => Carbon::today()->subMonths(2),
            'bill_type' => 'bee_comfort',
            'total_amount' => 4000,
        ]);

        // Test Today
        $respToday = $this->get(route('dashboard', ['period' => 'today']));
        $respToday->assertOk();
        $respToday->assertSee('Today');
        $respToday->assertSee('7 Days');
        $respToday->assertSee('Month');
        $respToday->assertSee('6 Months');
        $respToday->assertSee('Year');

        // Test 7 Days
        $resp7 = $this->get(route('dashboard', ['period' => '7_days']));
        $resp7->assertOk();

        // Test Month
        $respMonth = $this->get(route('dashboard', ['period' => 'month']));
        $respMonth->assertOk();

        // Test 6 Months
        $resp6m = $this->get(route('dashboard', ['period' => '6_months']));
        $resp6m->assertOk();

        // Test Year
        $respYear = $this->get(route('dashboard', ['period' => 'year']));
        $respYear->assertOk();
    }
}
