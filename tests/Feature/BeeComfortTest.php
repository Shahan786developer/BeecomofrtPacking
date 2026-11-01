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
            'invoice_no' => 'INV-TEST-0001',
            'invoice_date' => date('Y-m-d'),
            'discount' => 10,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $soldQty,
                ],
            ],
        ]);

        $sale = Sale::where('invoice_no', 'INV-TEST-0001')->first();
        $this->assertNotNull($sale);
        $response->assertRedirect(route('sales.index'));

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
            'invoice_no' => 'INV-TEST-OVERSOLD',
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
            'invoice_no' => 'INV-TEST-FOR-RET',
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
            'sale_id' => $sale->id,
            'return_no' => 'RET-TEST-FAIL',
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
            'sale_id' => $sale->id,
            'return_no' => 'RET-TEST-SUCCESS',
            'return_date' => date('Y-m-d'),
            'reason' => 'Defective packaging',
            'items' => [
                [
                    'sale_detail_id' => $saleDetail->id,
                    'quantity' => 4,
                ],
            ],
        ]);

        $return = ReturnRecord::where('return_no', 'RET-TEST-SUCCESS')->first();
        $this->assertNotNull($return);
        $responseSuccess->assertRedirect(route('returns.index'));

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
     * Test Thermal Barcode Configuration and Second-Line Description
     */
    public function test_barcode_configuration_and_print(): void
    {
        $product = Product::first();
        $desc = BarcodeDescription::where('name', 'INDIA')->first();
        $config = BarcodeConfiguration::getActive();

        $response = $this->post(route('barcode.print'), [
            'product_id' => $product->id,
            'description_id' => $desc->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertSee('BEE COMFORT');
        $response->assertSee("INDIA {$product->product_code}");
        $response->assertSee($product->sku_code);
    }
}
