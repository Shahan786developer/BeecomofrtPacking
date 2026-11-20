<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSeederAndFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Administrator', 'password' => bcrypt('waqar$1234'), 'role' => 'superadmin', 'is_active' => true]
        );
        $this->superAdmin->role = 'superadmin';
        $this->superAdmin->is_active = true;
        $this->superAdmin->save();
    }

    public function test_customer_creation_and_update_with_code_ntn_strn(): void
    {
        $this->actingAs($this->superAdmin);

        // 1. Create Customer with code, ntn, strn
        $response = $this->post(route('customers.store'), [
            'name' => 'Tariq Textiles',
            'code' => 'B999',
            'owner_name' => 'Tariq Mehmood',
            'phone' => '03001234567',
            'city' => 'Gujranwala',
            'address' => 'Main Bazar',
            'ntn' => '1234567-8',
            'strn' => '12-34-5678-901-23',
            'credit_days' => null,
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Tariq Textiles',
            'code' => 'B999',
            'owner_name' => 'Tariq Mehmood',
            'ntn' => '1234567-8',
            'strn' => '12-34-5678-901-23',
            'credit_days' => null,
        ]);

        $customer = Customer::where('code', 'B999')->firstOrFail();

        // 2. Update Customer
        $updateResp = $this->put(route('customers.update', $customer), [
            'name' => 'Tariq Textiles Pvt Ltd',
            'code' => 'B999',
            'owner_name' => 'Chaudhry Tariq',
            'phone' => '03001234567',
            'city' => 'Gujranwala',
            'address' => 'Model Town',
            'ntn' => '9876543-2',
            'strn' => '99-88-7766-554-32',
            'credit_days' => 15,
        ]);

        $updateResp->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Tariq Textiles Pvt Ltd',
            'owner_name' => 'Chaudhry Tariq',
            'ntn' => '9876543-2',
            'strn' => '99-88-7766-554-32',
            'credit_days' => 15,
        ]);
    }

    public function test_customer_search_by_code_ntn_and_strn(): void
    {
        $this->actingAs($this->superAdmin);

        Customer::create([
            'name' => 'Alpha Brothers',
            'code' => 'B888',
            'owner_name' => 'Alpha Khan',
            'ntn' => 'NTN-ALPHA-123',
            'strn' => 'STRN-ALPHA-456',
        ]);

        // Search by code
        $respCode = $this->get(route('customers.index', ['search' => 'B888']));
        $respCode->assertOk();
        $respCode->assertSee('Alpha Brothers');
        $respCode->assertSee('B888');

        // Search by NTN
        $respNtn = $this->get(route('customers.index', ['search' => 'NTN-ALPHA-123']));
        $respNtn->assertOk();
        $respNtn->assertSee('Alpha Brothers');

        // Search by STRN
        $respStrn = $this->get(route('customers.index', ['search' => 'STRN-ALPHA-456']));
        $respStrn->assertOk();
        $respStrn->assertSee('Alpha Brothers');
    }

    public function test_customer_seeder_execution(): void
    {
        // Add a walk-in retail customer
        Customer::create([
            'name' => 'Walk-in Retail Customer',
            'code' => 'B001',
            'phone' => '03001234567',
        ]);

        $seeder = new CustomerSeeder();
        $seeder->run();

        // 1. Walk-in code should be changed to B000
        $walkIn = Customer::where('name', 'Walk-in Retail Customer')->firstOrFail();
        $this->assertEquals('B000', $walkIn->code);

        // 2. Real Singhar Garments should have code B001
        $singhar = Customer::where('code', 'B001')->firstOrFail();
        $this->assertEquals('SINGHAR GARMENTS', $singhar->name);
        $this->assertEquals('PAPPU', $singhar->owner_name);
        $this->assertEquals('03016861786', $singhar->phone);
        $this->assertNull($singhar->credit_days); // Recovery default None

        // 3. Typo in B073 fixed
        $b073 = Customer::where('code', 'B073')->firstOrFail();
        $this->assertEquals('03378640444', $b073->phone);

        // 4. Test dummy customers should not exist
        $this->assertDatabaseMissing('customers', ['name' => 'XYZ']);
        $this->assertDatabaseMissing('customers', ['name' => 'testing']);

        // 5. Total real customers seeded is 344 (+ 1 walk-in = 345)
        $this->assertEquals(345, Customer::count());
    }
}
