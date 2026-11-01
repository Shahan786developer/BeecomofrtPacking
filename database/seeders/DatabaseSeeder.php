<?php

namespace Database\Seeders;

use App\Models\BarcodeConfiguration;
use App\Models\BarcodeDescription;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Dozen;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Sku;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Super Admin User
        \App\Models\User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('waqar$1234'),
            ]
        );

        // 1. Categories
        $categories = [
            ['name' => 'Comfort Fit', 'description' => 'Premium everyday comfort line'],
            ['name' => 'Classic Cotton', 'description' => '100% pure combed cotton series'],
            ['name' => 'Silk Touch', 'description' => 'Ultra soft luxury microfibers'],
            ['name' => 'Active Sport', 'description' => 'Breathable athletic performance wear'],
            ['name' => 'Winter Thermal', 'description' => 'Heavy insulating thermal wear'],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['name']] = Category::create($cat);
        }

        // 2. Dozens (12 selected by default)
        $dozen12 = Dozen::create(['label' => '12 pieces', 'quantity' => 12, 'is_default' => true]);
        $dozen6  = Dozen::create(['label' => '6 pieces', 'quantity' => 6, 'is_default' => false]);
        $dozen24 = Dozen::create(['label' => '24 pieces', 'quantity' => 24, 'is_default' => false]);

        // 3. Percentage Pricing Records (multiple configurable options, user can mark any default)
        $pricingOptions = [
            ['name' => 'Economy 15%', 'percentage' => 15.00, 'is_default' => false],
            ['name' => 'Retail 20%', 'percentage' => 20.00, 'is_default' => false],
            ['name' => 'Wholesale 25%', 'percentage' => 25.00, 'is_default' => false],
            ['name' => 'Commercial 30%', 'percentage' => 30.00, 'is_default' => false],
            ['name' => 'Standard Markup 35%', 'percentage' => 35.00, 'is_default' => true],
            ['name' => 'Special 40%', 'percentage' => 40.00, 'is_default' => false],
            ['name' => 'Premium 45%', 'percentage' => 45.00, 'is_default' => false],
            ['name' => 'Luxury 50%', 'percentage' => 50.00, 'is_default' => false],
            ['name' => 'Export 55%', 'percentage' => 55.00, 'is_default' => false],
            ['name' => 'High Margin 60%', 'percentage' => 60.00, 'is_default' => false],
        ];

        $defaultPricingRule = null;
        foreach ($pricingOptions as $rule) {
            $created = PricingRule::create([
                'name' => $rule['name'],
                'rule_type' => 'percentage',
                'percentage' => $rule['percentage'],
                'is_default' => $rule['is_default'],
                'description' => "Standard {$rule['percentage']}% percentage pricing rule",
            ]);
            if ($rule['is_default']) {
                $defaultPricingRule = $created;
            }
        }

        // Category-based pricing configuration
        PricingRule::create([
            'name' => 'Comfort Fit Category Rule (35%)',
            'rule_type' => 'category',
            'category_id' => $catModels['Comfort Fit']->id,
            'percentage' => 35.00,
            'is_default' => false,
            'description' => 'Category-specific pricing rule for Comfort Fit',
        ]);

        PricingRule::create([
            'name' => 'Winter Thermal Category Rule (45%)',
            'rule_type' => 'category',
            'category_id' => $catModels['Winter Thermal']->id,
            'percentage' => 45.00,
            'is_default' => false,
            'description' => 'Category-specific pricing rule for Winter Thermal',
        ]);

        // 4. Barcode Descriptions (Second line: manually selectable from searchable dropdown e.g. CHINA, INDIA)
        $descChina = BarcodeDescription::create(['name' => 'CHINA', 'is_default' => false]);
        $descIndia = BarcodeDescription::create(['name' => 'INDIA', 'is_default' => true]);
        BarcodeDescription::create(['name' => 'PAKISTAN', 'is_default' => false]);
        BarcodeDescription::create(['name' => 'TURKEY', 'is_default' => false]);
        BarcodeDescription::create(['name' => 'BANGLADESH', 'is_default' => false]);

        // Barcode Configuration
        BarcodeConfiguration::create([
            'brand_name' => 'BEE COMFORT',
            'label_width_mm' => 32.00,
            'label_height_mm' => 22.00,
            'barcode_type' => 'Code39',
            'font_size' => 8,
            'font_family' => 'Arabic Transparent',
            'text_align' => 'center',
            'rotation' => 0,
            'auto_resize' => true,
            'default_description_id' => $descIndia->id,
        ]);

        // 5. Customers
        Customer::create([
            'name' => 'Walk-in Retail Customer',
            'phone' => '+92 300 1234567',
            'email' => 'walkin@beecomfort.local',
            'address' => 'Local Cash Counter',
        ]);
        Customer::create([
            'name' => 'Ahmed Trading Co.',
            'phone' => '+92 321 9876543',
            'email' => 'ahmed@tradingco.com',
            'address' => 'Shop #14, Textile Plaza, Karachi',
        ]);
        Customer::create([
            'name' => 'Royal Garments Ltd.',
            'phone' => '+92 333 4567890',
            'email' => 'info@royalgarments.pk',
            'address' => 'Plot 88, Industrial Area, Lahore',
        ]);

        // 6. Registered Sample Products
        // Product 1: Comfort Fit (12 pieces) AA-01
        $sku1 = Sku::allocate();
        $p1Actual = 650.00;
        $p1Rule = $defaultPricingRule;
        $p1Selling = $p1Rule->calculateSellingPrice($p1Actual); // 650 * 1.35 = 877.5 -> 880
        $p1 = Product::create([
            'bill_no' => 'BILL-2026-001',
            'category_id' => $catModels['Comfort Fit']->id,
            'dozen_id' => $dozen12->id,
            'product_code' => 'AA-01',
            'sku_id' => $sku1->id,
            'sku_code' => $sku1->sku_code,
            'actual_price' => $p1Actual,
            'pricing_rule_id' => $p1Rule->id,
            'selling_price' => $p1Selling,
            'stock_quantity' => 120,
            'zero_stock_date' => null,
        ]);
        $p1->stockMovements()->create([
            'type' => 'initial',
            'quantity_change' => 120,
            'previous_stock' => 0,
            'new_stock' => 120,
            'notes' => 'Initial registered stock from Bill #BILL-2026-001',
        ]);

        // Product 2: Classic Cotton (12 pieces) CC-02
        $sku2 = Sku::allocate();
        $p2Actual = 900.00; // 900 * 1.25 = 1125 -> 1130
        $p2Rule = PricingRule::where('percentage', 25.00)->first();
        $p2Selling = $p2Rule->calculateSellingPrice($p2Actual);
        $p2 = Product::create([
            'bill_no' => 'BILL-2026-002',
            'category_id' => $catModels['Classic Cotton']->id,
            'dozen_id' => $dozen12->id,
            'product_code' => 'CC-02',
            'sku_id' => $sku2->id,
            'sku_code' => $sku2->sku_code,
            'actual_price' => $p2Actual,
            'pricing_rule_id' => $p2Rule->id,
            'selling_price' => $p2Selling,
            'stock_quantity' => 45,
            'zero_stock_date' => null,
        ]);
        $p2->stockMovements()->create([
            'type' => 'initial',
            'quantity_change' => 45,
            'previous_stock' => 0,
            'new_stock' => 45,
            'notes' => 'Initial registered stock from Bill #BILL-2026-002',
        ]);

        // Product 3: Active Sport (6 pieces) AS-03 (Zero-stock depleted for over 1 month to demonstrate requirement #6 and #7)
        $sku3 = Sku::allocate();
        $p3Actual = 400.00;
        $p3Selling = $defaultPricingRule->calculateSellingPrice($p3Actual);
        $depletedDate = Carbon::now()->subDays(34); // Over 1 month ago
        $p3 = Product::create([
            'bill_no' => 'BILL-2026-003',
            'category_id' => $catModels['Active Sport']->id,
            'dozen_id' => $dozen6->id,
            'product_code' => 'AS-03',
            'sku_id' => $sku3->id,
            'sku_code' => $sku3->sku_code,
            'actual_price' => $p3Actual,
            'pricing_rule_id' => $defaultPricingRule->id,
            'selling_price' => $p3Selling,
            'stock_quantity' => 0,
            'zero_stock_date' => $depletedDate,
        ]);
        $p3->stockMovements()->create([
            'type' => 'initial',
            'quantity_change' => 0,
            'previous_stock' => 0,
            'new_stock' => 0,
            'notes' => 'Stock depleted on ' . $depletedDate->format('Y-m-d'),
        ]);

        // Product 4: Winter Thermal (12 pieces) WT-04 (Negative stock for 38 days)
        $sku4 = Sku::allocate();
        $p4Actual = 1200.00;
        $p4Selling = $defaultPricingRule->calculateSellingPrice($p4Actual);
        $depletedDate4 = Carbon::now()->subDays(38);
        $p4 = Product::create([
            'bill_no' => 'BILL-2026-004',
            'category_id' => $catModels['Winter Thermal']->id,
            'dozen_id' => $dozen12->id,
            'product_code' => 'WT-04',
            'sku_id' => $sku4->id,
            'sku_code' => $sku4->sku_code,
            'actual_price' => $p4Actual,
            'pricing_rule_id' => $defaultPricingRule->id,
            'selling_price' => $p4Selling,
            'stock_quantity' => -3,
            'zero_stock_date' => $depletedDate4,
        ]);
        $p4->stockMovements()->create([
            'type' => 'initial',
            'quantity_change' => -3,
            'previous_stock' => 0,
            'new_stock' => -3,
            'notes' => 'Oversold back-order stock',
        ]);

        // 7. Seed Sample Sale Invoice
        $customer1 = Customer::first();
        $sale = \App\Models\Sale::create([
            'customer_id' => $customer1->id,
            'invoice_no' => 'INV-2026-0001',
            'invoice_date' => Carbon::now()->subDays(2),
            'subtotal' => $p1Selling * 2,
            'discount' => 10.00,
            'total_amount' => ($p1Selling * 2) - 10.00,
            'notes' => 'Customer retail delivery',
        ]);

        $saleDetail = $sale->details()->create([
            'product_id' => $p1->id,
            'sku_code' => $p1->sku_code,
            'product_title' => $p1->title,
            'unit_price' => $p1Selling,
            'quantity' => 2,
            'total_amount' => $p1Selling * 2,
        ]);

        $p1->decrement('stock_quantity', 2);
        $p1->stockMovements()->create([
            'type' => 'sale',
            'quantity_change' => -2,
            'previous_stock' => 120,
            'new_stock' => 118,
            'notes' => 'Sales invoice #' . $sale->invoice_no,
        ]);

        // 8. Seed Sample Bill Return
        $return = \App\Models\ReturnRecord::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer1->id,
            'return_no' => 'RET-2026-0001',
            'return_date' => Carbon::now()->subDay(),
            'total_amount' => $p1Selling,
            'reason' => 'Customer requested size exchange',
        ]);

        $return->details()->create([
            'sale_detail_id' => $saleDetail->id,
            'product_id' => $p1->id,
            'sku_code' => $p1->sku_code,
            'product_title' => $p1->title,
            'unit_price' => $p1Selling,
            'quantity' => 1,
            'total_amount' => $p1Selling,
        ]);

        $p1->increment('stock_quantity', 1);
        $p1->stockMovements()->create([
            'type' => 'return',
            'quantity_change' => 1,
            'previous_stock' => 118,
            'new_stock' => 119,
            'notes' => 'Customer return voucher #' . $return->return_no,
        ]);
    }
}
