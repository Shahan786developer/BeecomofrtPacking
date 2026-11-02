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
     * Generate an atomic, randomly chosen, guaranteed unique 7-digit numeric SKU.
     */
    public static function generateNextUniqueCode(): string
    {
        return DB::transaction(function () {
            do {
                $randomNum = random_int(1000000, 9999999);
                $code = (string) $randomNum;
            } while (DB::table('skus')->where('sku_code', $code)->lockForUpdate()->exists());

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
