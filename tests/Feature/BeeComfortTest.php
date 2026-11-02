<?php

namespace Tests\Feature;

use App\Models\BarcodeConfiguration;
use App\Models\BarcodeDescription;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Dozen;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Sku;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeeComfortTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $admin = \App\Models\User::where('email', 'superadmin@gmail.com')->first();
        if ($admin) {
            $this->actingAs($admin);
        }
    }

    /**
     * Test Selling Price Calculation formula:
     * Selling Price = CEILING((Actual Price * (1 + Percentage / 100)) / 10) * 10
     * Examples: 900->900, 901->910, 902->910, 905->910, 907->910, 915->920, 989->990
     */
    public function test_selling_price_calculation_formula(): void
    {
        // 0% markup test
        $this->assertEquals(900.0, PricingRule::calculateFromValues(900.0, 0));
        $this->assertEquals(910.0, PricingRule::calculateFromValues(901.0, 0));
        $this->assertEquals(910.0, PricingRule::calculateFromValues(902.0, 0));
        $this->assertEquals(910.0, PricingRule::calculateFromValues(905.0, 0));
        $this->assertEquals(910.0, PricingRule::calculateFromValues(907.0, 0));
        $this->assertEquals(920.0, PricingRule::calculateFromValues(915.0, 0));
        $this->assertEquals(990.0, PricingRule::calculateFromValues(989.0, 0));

        // Multiples of 10 remain unchanged
        $this->assertEquals(1000.0, PricingRule::calculateFromValues(1000.0, 0));

        // With 35% markup on 650: 650 * 1.35 = 877.5 -> Ceil(87.75)*10 = 880
        $this->assertEquals(880.0, PricingRule::calculateFromValues(650.0, 35));
    }

    /**
     * Test dynamic unified Product Title:
     * Category, Dozens, and Product Code are combined dynamically throughout the application.
     * Example: Comfort Fit (12 pieces) AA-01
     */
    public function test_dynamic_product_title_accessor(): void
    {
        $category = Category::where('name', 'Comfort Fit')->first();
        $dozen = Dozen::where('quantity', 12)->first();
        $sku = Sku::allocate();
        $rule = PricingRule::where('percentage', 35.0)->first();

        $product = Product::create([
            'bill_no' => 'BILL-TEST-1',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'product_code' => 'AA-01',
            'sku_id' => $sku->id,
            'sku_code' => $sku->sku_code,
            'actual_price' => 650.0,
            'pricing_rule_id' => $rule->id,
            'selling_price' => 880.0,
            'stock_quantity' => 50,
        ]);

        $this->assertEquals('Comfort Fit (12 pieces) AA-01', $product->title);
    }

    /**
     * Test automatic 7-digit SKU generation and uniqueness
     */
    public function test_automatic_seven_digit_sku_generation(): void
    {
        $sku1 = Sku::allocate();
        $sku2 = Sku::allocate();

        $this->assertEquals(7, strlen($sku1->sku_code));
        $this->assertEquals(7, strlen($sku2->sku_code));
        $this->assertNotEquals($sku1->sku_code, $sku2->sku_code);
        $this->assertTrue(is_numeric($sku1->sku_code));
    }

    /**
     * Test Product Registration Form submission via controller
     */
    public function test_product_registration(): void
    {
        $category = Category::first();
        $dozen = Dozen::where('quantity', 12)->first();
        $rule = PricingRule::first();

        $response = $this->post(route('products.store'), [
            'bill_no' => 'BILL-NEW-99',
            'parent_category' => 'Bee Comfort',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'product_code' => 'bb-99', // Lowercase should be saved as uppercase BB-99
            'actual_price' => 500,
            'pricing_rule_id' => $rule->id,
            'quantity' => 60,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('product_code', 'BB-99')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Bee Comfort', $product->parent_category);
        $this->assertEquals(7, strlen($product->sku_code));
        $this->assertEquals(60, $product->stock_quantity);

        // Check stock movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'initial',
            'quantity_change' => 60,
            'new_stock' => 60,
        ]);
    }

    /**
     * Test Sales Invoice:
     * - Saves invoice and invoice details
     * - Deducts sold quantity from product stock
     * - Maintains negative stock accurately if oversold
     * - Records zero_stock_date
     * - Saves stock movement history
     */
    public function test_sales_invoice_creation_and_stock_deduction(): void
    {
        $product = Product::where('stock_quantity', '>', 5)->first();
        $initialStock = $product->stock_quantity;
        $customer = Customer::first();

        $soldQty = 3;

        $response = $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'BC-INV-TEST-0001',
            'invoice_date' => date('Y-m-d'),
            'discount' => 10,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $soldQty,
                ],
            ],
        ]);

        $sale = Sale::where('invoice_no', 'BC-INV-TEST-0001')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('bee_comfort', $sale->bill_type);
        $response->assertRedirect(route('sales.index', ['bill_type' => 'bee_comfort']));

        // Verify stock deduction
        $product->refresh();
        $this->assertEquals($initialStock - $soldQty, $product->stock_quantity);

        // Verify stock movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity_change' => -$soldQty,
            'new_stock' => $initialStock - $soldQty,
        ]);

        // Test overselling into negative stock
        $excessSold = $product->stock_quantity + 5; // Will make stock -5
        $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'BC-INV-TEST-OVERSOLD',
            'invoice_date' => date('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $excessSold,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(-5, $product->stock_quantity);
        $this->assertNotNull($product->zero_stock_date);
    }

    /**
     * Test Bill Return:
     * - Linked to original invoice
     * - Prevents returning more than sold
     * - Restores stock correctly
     * - Logs stock movement
     */
    public function test_bill_return_and_stock_restoration(): void
    {
        $product = Product::first();
        $customer = Customer::first();

        // 1. Create a sale of 10 units
        $sale = Sale::create([
            'invoice_no' => 'BC-INV-TEST-FOR-RET',
            'bill_type' => 'bee_comfort',
            'customer_id' => $customer->id,
            'invoice_date' => date('Y-m-d'),
            'subtotal' => $product->selling_price * 10,
            'total_amount' => $product->selling_price * 10,
        ]);

        $saleDetail = SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'sku_code' => $product->sku_code,
            'unit_price' => $product->selling_price,
            'quantity' => 10,
            'returned_quantity' => 0,
            'total_amount' => $product->selling_price * 10,
        ]);

        $stockBeforeReturn = $product->fresh()->stock_quantity;

        // 2. Try returning 15 units (exceeds 10 eligible) -> should fail
        $responseFail = $this->post(route('returns.store'), [
            'return_type' => 'bee_comfort',
            'sale_id' => $sale->id,
            'return_no' => 'BC-RET-TEST-FAIL',
            'return_date' => date('Y-m-d'),
            'items' => [
                [
                    'sale_detail_id' => $saleDetail->id,
                    'quantity' => 15,
                ],
            ],
        ]);
        $responseFail->assertSessionHasErrors('items');

        // 3. Return 4 units (valid)
        $responseSuccess = $this->post(route('returns.store'), [
            'return_type' => 'bee_comfort',
            'sale_id' => $sale->id,
            'return_no' => 'BC-RET-TEST-SUCCESS',
            'return_date' => date('Y-m-d'),
            'reason' => 'Defective packaging',
            'items' => [
                [
                    'sale_detail_id' => $saleDetail->id,
                    'quantity' => 4,
                ],
            ],
        ]);

        $return = ReturnRecord::where('return_no', 'BC-RET-TEST-SUCCESS')->first();
        $this->assertNotNull($return);
        $this->assertEquals('bee_comfort', $return->return_type);
        $responseSuccess->assertRedirect(route('returns.index', ['return_type' => 'bee_comfort']));

        // Check stock restored
        $product->refresh();
        $this->assertEquals($stockBeforeReturn + 4, $product->stock_quantity);

        // Check sale detail returned_quantity updated
        $saleDetail->refresh();
        $this->assertEquals(4, $saleDetail->returned_quantity);
        $this->assertEquals(6, $saleDetail->eligible_return_quantity);
    }

    /**
     * Test one-month zero/negative stock notification condition
     */
    public function test_one_month_zero_negative_stock_notification(): void
    {
        $depletedProduct = Product::where('stock_quantity', '<=', 0)
            ->whereNotNull('zero_stock_date')
            ->first();

        $this->assertNotNull($depletedProduct);
        $this->assertTrue($depletedProduct->isDepletedForOneMonth());
        $this->assertGreaterThanOrEqual(30, $depletedProduct->depleted_days);

        // Test dashboard loads and contains notification
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Critical Attention: One-Month Depleted Stock Alert');
        $response->assertSee($depletedProduct->sku_code);
    }

    /**
     * Test Thermal Barcode Generation and Auto-Fetched Second-Line
     */
    public function test_barcode_configuration_and_print(): void
    {
        $product = Product::with('category')->first();
        $config = BarcodeConfiguration::getActive();

        $response = $this->post(route('barcode.print'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertSee('BEE COMFORT');
        $response->assertSee("{$product->category->name} {$product->product_code}");
        $response->assertSee($product->sku_code);
    }

    /**
     * Test Customer creation with Owner Name and 4 Phone numbers
     */
    public function test_customer_creation_with_owner_and_four_phones(): void
    {
        $response = $this->post(route('customers.store'), [
            'name' => 'Royal Garments International',
            'owner_name' => 'Haji Muhammad Iqbal',
            'phone' => '03001234567',
            'phone_2' => '03217654321',
            'phone_3' => '03459876543',
            'phone_4' => '03123456789',
            'city' => 'Faisalabad',
            'address' => 'Clock Tower Market Shop 44',
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::where('name', 'Royal Garments International')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Haji Muhammad Iqbal', $customer->owner_name);
        $this->assertEquals('03001234567', $customer->phone);
        $this->assertEquals('03217654321', $customer->phone_2);
        $this->assertEquals('03459876543', $customer->phone_3);
        $this->assertEquals('03123456789', $customer->phone_4);
    }

    /**
     * Test A4 Printable Invoice View
     */
    public function test_a4_printable_invoice_layout(): void
    {
        $sale = Sale::first();
        $response = $this->get(route('sales.print', $sale));

        $response->assertStatus(200);
        $response->assertSee('Bee Comfort');
        $response->assertSee($sale->invoice_no);
        $response->assertSee($sale->customer->name);
        $response->assertSee('Print Invoice (A4)');
    }

    /**
     * Test Customer Ledger History and Account Metrics
     */
    public function test_customer_ledger_history_and_metrics(): void
    {
        $customer = Customer::first();
        $response = $this->get(route('customers.ledger', $customer));

        $response->assertStatus(200);
        $response->assertSee($customer->name);
        $response->assertSee('Total Invoices');
        $response->assertSee('Total Purchased');
        $response->assertSee('Total Returns');
        $response->assertSee('Customer Transaction History');
    }

    /**
     * Test Authentication Protection: unauthenticated visitor cannot access system
     */
    public function test_unauthenticated_user_redirects_to_login(): void
    {
        auth()->logout();

        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response2 = $this->get('/products');
        $response2->assertRedirect('/login');
    }

    /**
     * Test Super Admin Login with exact provided credentials
     */
    public function test_super_admin_authentication_and_credentials(): void
    {
        auth()->logout();

        // Failed attempt with wrong password
        $failResponse = $this->post(route('login.post'), [
            'email' => 'superadmin@gmail.com',
            'password' => 'wrongpass',
        ]);
        $failResponse->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());

        // Successful login with superadmin@gmail.com / waqar$1234
        $successResponse = $this->post(route('login.post'), [
            'email' => 'superadmin@gmail.com',
            'password' => 'waqar$1234',
        ]);
        $successResponse->assertRedirect('/dashboard');
        $this->assertTrue(auth()->check());
        $this->assertEquals('superadmin@gmail.com', auth()->user()->email);

        // Logout
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect('/login');
        $this->assertFalse(auth()->check());
    }

    /**
     * Test Dozen Unit Management and Default Pre-selection
     */
    public function test_dozens_unit_management_and_default(): void
    {
        // Add new dozen unit
        $response = $this->post(route('dozens.store'), [
            'label' => '18 pieces',
            'quantity' => 18,
            'is_default' => 1,
        ]);
        $response->assertRedirect(route('dozens.index'));

        $dozen = Dozen::where('label', '18 pieces')->first();
        $this->assertNotNull($dozen);
        $this->assertTrue($dozen->is_default);

        // Verify product create page pre-selects this default
        $createPage = $this->get(route('products.create'));
        $createPage->assertStatus(200);
        $createPage->assertSee('18 pieces');
    }

    /**
     * Test Cash Receipt Voucher (CRV) Flow and Real-time Balance Calculation:
     * User scenario: Customer has 2800 bill, pays 1000 via CRV, remaining balance is 1800.
     */
    public function test_cash_receipt_voucher_crv_and_customer_balance_calculation(): void
    {
        $customer = Customer::create([
            'name' => 'Al-Karam Garments',
            'owner_name' => 'Sheikh Waqas',
            'phone' => '03001234567',
            'city' => 'Lahore',
        ]);

        $product = Product::first();

        // 1. Create Sale Invoice of Rs. 2,800.00
        $sale = Sale::create([
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'INV-TEST-2800',
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::today(),
            'subtotal' => 2800.00,
            'discount_amount' => 0.00,
            'total_amount' => 2800.00,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'sku_code' => $product->sku_code,
            'quantity' => 1,
            'unit_price' => 2800.00,
            'total_amount' => 2800.00,
        ]);

        // Customer has Rs. 2800.00 pending
        $customer->refresh();
        $this->assertEquals(2800.00, $customer->total_invoiced);
        $this->assertEquals(0.00, $customer->total_paid);
        $this->assertEquals(2800.00, $customer->balance_due);

        // 2. Customer pays Rs. 1,000.00 via Cash Receipt Voucher (CRV)
        $voucherNo = \App\Models\CashReceipt::generateNextVoucherNo();
        $this->assertStringStartsWith('CRV-', $voucherNo);

        $response = $this->post(route('receipts.store'), [
            'voucher_no' => $voucherNo,
            'customer_id' => $customer->id,
            'amount' => 1000.00,
            'payment_date' => Carbon::today()->format('Y-m-d'),
            'payment_method' => 'cash',
            'notes' => 'Advance partial bill payment',
        ]);

        $response->assertRedirect();

        // 3. Verify Customer balance is now exactly Rs. 1,800.00
        $customer->refresh();
        $this->assertEquals(2800.00, $customer->total_invoiced);
        $this->assertEquals(1000.00, $customer->total_paid);
        $this->assertEquals(1800.00, $customer->balance_due);

        // 4. Verify Customer Ledger includes the CRV entry and correct Running Balance
        $ledgerResponse = $this->get(route('customers.ledger', $customer));
        $ledgerResponse->assertStatus(200);
        $ledgerResponse->assertSee($voucherNo);
        $ledgerResponse->assertSee('1,800.00');
    }

    /**
     * Test Company Profile Settings update and dynamic Sale Invoice print branding
     */
    public function test_company_profile_settings_and_dynamic_invoice_print(): void
    {
        $updateResponse = $this->post(route('settings.company.update'), [
            'company_name' => 'Bee Comfort Premium Garments',
            'tagline' => 'Wholesale Packaging Solutions',
            'warehouse_address' => 'Plot #45, Industrial Estate, Faisalabad',
            'phone_primary' => '+92 321 7654321',
            'email' => 'sales@beecomfort.com',
            'invoice_terms' => 'Custom Terms: Goods once sold cannot be returned without original barcode tag.',
        ]);

        $updateResponse->assertRedirect(route('settings.company'));

        $company = \App\Models\CompanyProfile::getActive();
        $this->assertEquals('Bee Comfort Premium Garments', $company->company_name);
        $this->assertEquals('+92 321 7654321', $company->phone_primary);

        // Verify print view for sale uses updated company profile
        $sale = Sale::first();
        if ($sale) {
            $printResponse = $this->get(route('sales.print', $sale));
            $printResponse->assertStatus(200);
            $printResponse->assertSee('Bee Comfort Premium Garments');
            $printResponse->assertSee('+92 321 7654321');
            $printResponse->assertSee('Plot #45, Industrial Estate, Faisalabad');
        }
    }

    /**
     * Test Printable Customer Statement View
     */
    public function test_customer_printable_statement(): void
    {
        $customer = Customer::first();
        $response = $this->get(route('customers.print-statement', $customer));
        $response->assertStatus(200);
        $response->assertSee('STATEMENT');
        $response->assertSee($customer->name);
    }

    /**
     * Test Locked Sale Return flow when initiated from Sale Invoice
     */
    public function test_locked_sale_return_flow(): void
    {
        $sale = Sale::first();
        if ($sale) {
            $response = $this->get(route('returns.create', [
                'sale_id' => $sale->id,
                'return_type' => $sale->bill_type ?? 'bee_comfort',
            ]));

            $response->assertStatus(200);
            $response->assertSee($sale->invoice_no);
            $response->assertSee($sale->customer->name);
            $response->assertSee('Locked to Original Invoice');
        }
    }

    /**
     * Test Separate China & Bee Comfort Bill creation pages and strict catalog product isolation
     */
    public function test_separate_china_and_bee_comfort_bill_pages_and_strict_product_filtering(): void
    {
        $category = Category::first();
        $dozen = Dozen::first();
        $pricingRule = PricingRule::first();
        $skuChina = Sku::allocate();
        $skuBee = Sku::allocate();

        // Create China Product
        $chinaProduct = Product::create([
            'bill_no' => 'BILL-CH-01',
            'parent_category' => 'China',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $pricingRule->id,
            'product_code' => 'CH-999',
            'sku_id' => $skuChina->id,
            'sku_code' => $skuChina->sku_code,
            'actual_price' => 500,
            'selling_price' => 600,
            'stock_quantity' => 100,
        ]);

        // Create Bee Comfort Product
        $beeProduct = Product::create([
            'bill_no' => 'BILL-BC-01',
            'parent_category' => 'Bee Comfort',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $pricingRule->id,
            'product_code' => 'BC-999',
            'sku_id' => $skuBee->id,
            'sku_code' => $skuBee->sku_code,
            'actual_price' => 700,
            'selling_price' => 850,
            'stock_quantity' => 100,
        ]);

        $customer = Customer::first();

        // 1. Visit China Bill Create Page
        $chinaPage = $this->get(route('sales.create', ['bill_type' => 'china']));
        $chinaPage->assertStatus(200);
        $chinaPage->assertSee('New China Sales Invoice');
        $chinaPage->assertSee('China Catalog Locked');
        $chinaPage->assertSee($chinaProduct->title);
        $chinaPage->assertDontSee($beeProduct->title);

        // 2. Visit Bee Comfort Bill Create Page
        $beePage = $this->get(route('sales.create', ['bill_type' => 'bee_comfort']));
        $beePage->assertStatus(200);
        $beePage->assertSee('New Bee Comfort Sales Invoice');
        $beePage->assertSee('Bee Comfort Catalog Locked');
        $beePage->assertSee($beeProduct->title);
        $beePage->assertDontSee($chinaProduct->title);

        // 3. API Barcode scan mismatch check
        $apiMismatch = $this->getJson("/api/products/by-sku/{$chinaProduct->sku_code}?bill_type=bee_comfort");
        $apiMismatch->assertStatus(200);
        $apiMismatch->assertJson(['found' => true, 'mismatch' => true]);

        // 4. Server-side validation prevents cross-category injection
        $invalidPost = $this->post(route('sales.store'), [
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'BC-INV-TEST-CROSS',
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::today()->format('Y-m-d'),
            'items' => [
                ['product_id' => $chinaProduct->id, 'quantity' => 1]
            ]
        ]);
        $invalidPost->assertSessionHasErrors('items');
    }

    /**
     * Test Separate China and Bee Comfort Returns Locking
     */
    public function test_separate_china_and_bee_comfort_returns_locking(): void
    {
        $customer = Customer::first();

        // Create Bee Comfort Sale
        $bcSale = Sale::create([
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'BC-TEST-INV-RET',
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::today(),
            'subtotal' => 1000,
            'total_amount' => 1000,
        ]);

        $prod = Product::first();
        SaleDetail::create([
            'sale_id' => $bcSale->id,
            'product_id' => $prod->id,
            'product_title' => $prod->title,
            'sku_code' => $prod->sku_code,
            'quantity' => 10,
            'returned_quantity' => 0,
            'unit_price' => 100,
            'total_amount' => 1000,
        ]);

        // Visit China Return Create Page: Bee Comfort Sale should NOT be in the China invoice list
        $chinaReturnPage = $this->get(route('returns.create', ['return_type' => 'china']));
        $chinaReturnPage->assertStatus(200);
        $chinaReturnPage->assertSee('Process China Bill Return');
        $chinaReturnPage->assertDontSee('BC-TEST-INV-RET');

        // Visit Bee Comfort Return Create Page: Bee Comfort Sale SHOULD be in the list
        $bcReturnPage = $this->get(route('returns.create', ['return_type' => 'bee_comfort']));
        $bcReturnPage->assertStatus(200);
        $bcReturnPage->assertSee('Process Bee Comfort Bill Return');
        $bcReturnPage->assertSee('BC-TEST-INV-RET');
    }

    /**
     * Test Dashboard Gross Profit (Actual Price vs Selling Price) and China vs Bee Comfort Comparison
     */
    public function test_dashboard_gross_profit_and_china_vs_bee_comfort_comparison(): void
    {
        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Gross Profit');
        $dashboard->assertSee('Bee Comfort vs China');
        $dashboard->assertSee('Performance');
        $dashboard->assertSee('categoryComparisonChart');
    }
}
