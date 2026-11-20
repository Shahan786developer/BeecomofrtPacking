<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Resolve code collision for existing demo / walk-in customer (e.g. ID 1 having B001)
        $walkIn = Customer::where('name', 'like', '%Walk-in%')->first();
        if ($walkIn) {
            $walkIn->update(['code' => 'B000']);
            $this->command?->info("Updated Walk-in Retail Customer code to B000.");
        }

        // Demo customers B002 / B003 update if they conflict with real dataset
        $demoAhmed = Customer::where('name', 'Ahmed Trading Co.')->where('code', 'B002')->first();
        if ($demoAhmed) {
            $demoAhmed->update(['code' => 'DEMO-002']);
        }
        $demoRoyal = Customer::where('name', 'Royal Garments Ltd.')->where('code', 'B003')->first();
        if ($demoRoyal) {
            $demoRoyal->update(['code' => 'DEMO-003']);
        }

        // 2. Locate CSV file (database/data/customers.csv or fallback)
        $csvPath = database_path('data/customers.csv');
        if (!File::exists($csvPath)) {
            $csvPath = 'C:\\Users\\PC\\Downloads\\customers.csv';
        }

        if (!File::exists($csvPath)) {
            $this->command?->error("Customer CSV file not found at: {$csvPath}");
            return;
        }

        $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            $this->command?->error("Customer CSV file is empty.");
            return;
        }

        $csv = array_map('str_getcsv', $lines);
        $header = array_shift($csv);

        // Dummy / Test customer IDs to skip
        $dummyOldIds = ['322', '323', '326', '1076', '2121'];

        $seenCodes = [];
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $duplicateCount = 0;

        foreach ($csv as $row) {
            if (count($row) < count($header)) {
                continue;
            }

            $item = array_combine($header, $row);
            $oldId = trim($item['id'] ?? '');

            // Skip dummy / test records
            if (in_array($oldId, $dummyOldIds)) {
                $skippedCount++;
                continue;
            }

            $rawName = trim($item['name'] ?? '');
            if ($rawName === '' || $rawName === 'NULL') {
                continue;
            }

            // Extract customer code and clean name e.g. "SINGHAR GARMENTS (B001)"
            $code = null;
            $name = $rawName;
            if (preg_match('/^(.*?)\s*\(([A-Za-z0-9\s\-]+)\)$/', $rawName, $matches)) {
                $name = trim($matches[1]);
                $code = strtoupper(trim($matches[2]));
            }

            // Deduplicate if this code has already been processed (e.g. duplicate rows B247 & B249)
            if ($code && isset($seenCodes[$code])) {
                $duplicateCount++;
                continue;
            }
            if ($code) {
                $seenCodes[$code] = true;
            }

            // Clean concern name -> owner_name
            $concern = trim($item['concern_name'] ?? '');
            $ownerName = ($concern === '' || $concern === 'NULL') ? null : $concern;

            // Clean mobile -> phone
            $rawMobile = trim($item['mobile'] ?? '');
            $phone = null;
            if ($rawMobile !== '' && $rawMobile !== 'NULL') {
                // Fix typo 'O' instead of '0', strip internal spaces
                $clean = str_ireplace('O', '0', $rawMobile);
                $clean = preg_replace('/\s+/', '', $clean);
                $phone = $clean ?: null;
            }

            // Clean city
            $rawCity = trim($item['city'] ?? '');
            $city = ($rawCity === '' || $rawCity === 'NULL') ? null : $rawCity;

            // Clean address
            $rawAddress = trim($item['address'] ?? '');
            $address = ($rawAddress === '' || $rawAddress === 'NULL') ? null : $rawAddress;

            // Clean NTN & STRN
            $rawNtn = trim($item['NTN'] ?? '');
            $ntn = ($rawNtn === '' || $rawNtn === 'NULL') ? null : $rawNtn;

            $rawStrn = trim($item['STRN'] ?? '');
            $strn = ($rawStrn === '' || $rawStrn === 'NULL') ? null : $rawStrn;

            // Recovery cycle / credit_days: default is NULL (None / Cash Basis)
            $creditDays = null;

            // Build payload
            $payload = [
                'name' => $name,
                'owner_name' => $ownerName,
                'phone' => $phone,
                'city' => $city,
                'address' => $address,
                'ntn' => $ntn,
                'strn' => $strn,
                'credit_days' => $creditDays,
            ];

            if ($code) {
                $payload['code'] = $code;
                $customer = Customer::updateOrCreate(['code' => $code], $payload);
            } else {
                $customer = Customer::updateOrCreate(['name' => $name, 'phone' => $phone], $payload);
            }

            if ($customer->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        $this->command?->info("Customer seeding finished:");
        $this->command?->info(" - Created: {$createdCount}");
        $this->command?->info(" - Updated: {$updatedCount}");
        $this->command?->info(" - Duplicates Skipped: {$duplicateCount}");
        $this->command?->info(" - Test/Dummy Skipped: {$skippedCount}");
        $this->command?->info(" - Total Active Customers in DB: " . Customer::count());
    }
}
