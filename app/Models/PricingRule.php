<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_type',
        'category_id',
        'percentage',
        'is_default',
        'description',
    ];

    protected $casts = [
        'percentage' => 'float',
        'is_default' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Calculate selling price:
     * Formula: Selling Price = CEILING((Actual Price * (1 + Percentage / 100)) / 10) * 10
     * If the calculated price is already a multiple of 10, keep it unchanged.
     */
    public function calculateSellingPrice(float $actualPrice): float
    {
        if ($actualPrice <= 0) {
            return 0.0;
        }

        $rawPrice = $actualPrice * (1 + ($this->percentage / 100));
        return (float) (ceil($rawPrice / 10) * 10);
    }

    public static function calculateFromValues(float $actualPrice, float $percentage): float
    {
        if ($actualPrice <= 0) {
            return 0.0;
        }

        $rawPrice = $actualPrice * (1 + ($percentage / 100));
        return (float) (ceil($rawPrice / 10) * 10);
    }
}
