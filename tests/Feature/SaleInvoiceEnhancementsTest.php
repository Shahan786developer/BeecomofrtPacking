<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Customer;
use App\Models\Dozen;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Sku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleInvoiceEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;
    protected Customer $customer;
    protected Product $beeProduct;
    protected Product $chinaProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('waqar$1234'), 'role' => 'superadmin', 'is_active' => true]
        );
        $this->superAdmin->role = 'superadmin';
        $this->superAdmin->save();

        $this->regularUser = User::firstOrCreate(
            ['email' => 'staff@gmail.com'],
            ['name' => 'Staff Member', 'password' => bcrypt('password123'), 'role' => 'staff', 'is_active' => true]
        );
        $this->regularUser->permissions()->updateOrCreate(
            ['module' => 'sales'],
            ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_print' => true]
        );

        $this->customer = Customer::firstOrCreate(
            ['name' => 'Metro Garments Hub'],
            [
                'code' => 55,
                'owner_name' => 'Zubair Khan',
                'phone' => '+92 300 9876543',
                'city' => 'Lahore',
                'address' => 'Mall Road, Lahore'
            ]
        );

        $category = Category::firstOrCreate(['name' => 'Summer Tee']);
        $dozen = Dozen::firstOrCreate(['label' => '12 pieces'], ['pieces' => 12]);
        $rule = PricingRule::firstOrCreate(['percentage' => 0], ['name' => 'Default']);

        $skuBee = Sku::create([
            'sku_code' => '9998881',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $rule->id,
        ]);
        $this->beeProduct = Product::create([
            'bill_no' => 'BC-BILL-10',
            'parent_category' => 'Bee Comfort',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $rule->id,
            'product_code' => 'BC-101',
            'sku_id' => $skuBee->id,
            'sku_code' => $skuBee->sku_code,
            'actual_price' => 500,
            'selling_price' => 600,
            'stock_quantity' => 100,
        ]);

        $skuChina = Sku::create([
            'sku_code' => '9998882',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $rule->id,
        ]);
        $this->chinaProduct = Product::create([
            'bill_no' => 'CH-BILL-10',
            'parent_category' => 'China',
            'category_id' => $category->id,
            'dozen_id' => $dozen->id,
            'pricing_rule_id' => $rule->id,
            'product_code' => 'CH-202',
            'sku_id' => $skuChina->id,
            'sku_code' => $skuChina->sku_code,
            'actual_price' => 400,
            'selling_price' => 550,
            'stock_quantity' => 80,
        ]);
    }

    /**
     * 1. Customer dropdown label contains Company Name, Owner, and City (not phone)
     */
    public function test_customer_dropdown_label_format(): void
    {
        $expected = "Metro Garments Hub — Owner: Zubair Khan • City: Lahore";
        $this->assertEquals($expected, $this->customer->search_dropdown_label);

        // Test fallback when owner or city is missing
        $cust2 = Customer::create(['name' => 'Solo Store']);
        $this->assertEquals("Solo Store — Owner: No Owner • City: No City", $cust2->search_dropdown_label);
    }

    /**
     * 2. Clean Product Title: Pieces and code must not appear in product title
     */
    public function test_product_clean_title_accessor(): void
    {
        // Raw title accessor has (12 pieces) BC-101
        $this->assertStringContainsString('(12 pieces)', $this->beeProduct->title);
        $this->assertStringContainsString('BC-101', $this->beeProduct->title);

        // clean_title should only have the category name without pieces or code
        $this->assertEquals('Summer Tee', $this->beeProduct->clean_title);
        $this->assertStringNotContainsString('(12 pieces)', $this->beeProduct->clean_title);
        $this->assertStringNotContainsString('BC-101', $this->beeProduct->clean_title);
    }

    /**
     * 3. Bee Comfort bill allows Super Admin to customize rate / unit price
     */
    public function test_superadmin_can_edit_rate_on_bee_comfort_bill(): void
    {
        $this->actingAs($this->superAdmin);

        $customPrice = 750.00; // Original selling_price is 600

        $response = $this->post(route('sales.store'), [
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'SIN-TEST-001',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'discount' => 0,
            'items' => [
                [
                    'product_id' => $this->beeProduct->id,
                    'quantity' => 2,
                    'unit_price' => $customPrice,
                ]
            ],
        ]);

        $response->assertRedirect(route('sales.index', ['bill_type' => 'bee_comfort']));

        $sale = Sale::where('invoice_no', 'SIN-TEST-001')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(1500.00, $sale->total_amount);

        $detail = $sale->details->first();
        $this->assertEquals($customPrice, $detail->unit_price);
        $this->assertEquals('Summer Tee', $detail->product_title);
    }

    /**
     * 4. Regular staff or non-superadmin cannot alter rate even on Bee Comfort bill
     */
    public function test_regular_user_cannot_alter_rate_on_bee_comfort_bill(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->post(route('sales.store'), [
            'bill_type' => 'bee_comfort',
            'invoice_no' => 'SIN-TEST-002',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'discount' => 0,
            'items' => [
                [
                    'product_id' => $this->beeProduct->id,
                    'quantity' => 2,
                    'unit_price' => 999.00, // Attempt custom rate
                ]
            ],
        ]);

        $response->assertRedirect(route('sales.index', ['bill_type' => 'bee_comfort']));

        $sale = Sale::where('invoice_no', 'SIN-TEST-002')->first();
        $this->assertNotNull($sale);
        // Selling price should remain default catalog price 600.00
        $this->assertEquals(1200.00, $sale->total_amount);
        $this->assertEquals(600.00, $sale->details->first()->unit_price);
    }

    /**
     * 5. China bill rate is strictly locked for all users
     */
    public function test_china_bill_rate_is_strictly_locked_even_for_superadmin(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('sales.store'), [
            'bill_type' => 'china',
            'invoice_no' => 'SIN-TEST-003',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'discount' => 0,
            'items' => [
                [
                    'product_id' => $this->chinaProduct->id,
                    'quantity' => 2,
                    'unit_price' => 999.00, // Attempt custom rate
                ]
            ],
        ]);

        $response->assertRedirect(route('sales.index', ['bill_type' => 'china']));

        $sale = Sale::where('invoice_no', 'SIN-TEST-003')->first();
        $this->assertNotNull($sale);
        // Selling price must remain locked at 550.00
        $this->assertEquals(1100.00, $sale->total_amount);
        $this->assertEquals(550.00, $sale->details->first()->unit_price);
    }

    /**
     * 6. Live customer balance API endpoint returns previous balance accurately
     */
    public function test_customer_live_balance_api(): void
    {
        $this->actingAs($this->superAdmin);

        // Initially balance is 0
        $res = $this->getJson("/api/customers/{$this->customer->id}/balance");
        $res->assertStatus(200);
        $res->assertJson(['balance' => 0.0]);

        // Create a sale of 1000
        Sale::create([
            'invoice_no' => 'INV-BAL-1',
            'bill_type' => 'bee_comfort',
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'subtotal' => 1000,
            'total_amount' => 1000,
        ]);

        $res2 = $this->getJson("/api/customers/{$this->customer->id}/balance");
        $res2->assertStatus(200);
        $res2->assertJson(['balance' => 1000.0]);
    }

    /**
     * 7. Print view conditionally shows previous balance and ink-friendly styles
     */
    public function test_invoice_print_view_previous_balance_and_styling(): void
    {
        $this->actingAs($this->superAdmin);

        // Sale with show_previous_balance = true
        $saleWithBal = Sale::create([
            'invoice_no' => 'INV-PRINT-01',
            'bill_type' => 'bee_comfort',
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'subtotal' => 1200,
            'total_amount' => 1200,
            'show_previous_balance' => true,
            'previous_balance' => 3500.00,
        ]);

        SaleDetail::create([
            'sale_id' => $saleWithBal->id,
            'product_id' => $this->beeProduct->id,
            'product_title' => $this->beeProduct->clean_title,
            'sku_code' => $this->beeProduct->sku_code,
            'unit_price' => 600,
            'quantity' => 2,
            'total_amount' => 1200,
        ]);

        $res = $this->get(route('sales.print', $saleWithBal));
        $res->assertStatus(200);

        // Company info
        $res->assertSee('Bee Comfort');

        // Customer card: Phone visible, Owner and City removed
        $res->assertSee($this->customer->phone);
        $res->assertDontSee('Owner:');
        $res->assertDontSee('City:');

        // Product title without pieces or code
        $res->assertSee('Summer Tee');
        $res->assertDontSee('(12 pieces)');
        $res->assertDontSee('BC-101');

        // Totals: Previous balance and Total Payable
        $res->assertSee('Previous Balance:');
        $res->assertSee('3,500.00');
        $res->assertSee('Total Payable:');
        $res->assertSee('4,700.00');

        // Check Sale with show_previous_balance = false
        $saleWithoutBal = Sale::create([
            'invoice_no' => 'INV-PRINT-02',
            'bill_type' => 'bee_comfort',
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'subtotal' => 1200,
            'total_amount' => 1200,
            'show_previous_balance' => false,
            'previous_balance' => 3500.00,
        ]);

        $res2 = $this->get(route('sales.print', $saleWithoutBal));
        $res2->assertStatus(200);
        $res2->assertDontSee('Previous Balance:');
        $res2->assertSee('Grand Total:');
        $res2->assertSee('1,200.00');
    }
}
