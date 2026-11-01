<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Sku extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku_code',
        'is_assigned',
    ];

    protected $casts = [
        'is_assigned' => 'boolean',
    ];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    /**
     * Generate an atomic, guaranteed unique 7-digit numeric SKU.
     */
    public static function generateNextUniqueCode(): string
    {
        return DB::transaction(function () {
            // Find max existing 7-digit numeric sku_code
            $maxSku = DB::table('skus')->lockForUpdate()->max('sku_code');

            if ($maxSku && is_numeric($maxSku)) {
                $nextNum = (int) $maxSku + 1;
            } else {
                $nextNum = 1000001; // Starting 7-digit number
            }

            // Ensure 7 numeric digits
            if ($nextNum > 9999999) {
                // Wrap around or find first available gap
                for ($candidate = 1000001; $candidate <= 9999999; $candidate++) {
                    $candCode = str_pad((string)$candidate, 7, '0', STR_PAD_LEFT);
                    if (!DB::table('skus')->where('sku_code', $candCode)->exists()) {
                        $nextNum = $candidate;
                        break;
                    }
                }
            }

            $code = str_pad((string) $nextNum, 7, '0', STR_PAD_LEFT);

            // Double check uniqueness
            while (DB::table('skus')->where('sku_code', $code)->exists()) {
                $nextNum++;
                $code = str_pad((string) $nextNum, 7, '0', STR_PAD_LEFT);
            }

            return $code;
        });
    }

    /**
     * Allocate and persist a unique SKU record
     */
    public static function allocate(): self
    {
        return DB::transaction(function () {
            $code = self::generateNextUniqueCode();
            return self::create([
                'sku_code' => $code,
                'is_assigned' => true,
            ]);
        });
    }
}
