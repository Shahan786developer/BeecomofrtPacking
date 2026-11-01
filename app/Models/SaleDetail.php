<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_title',
        'sku_code',
        'unit_price',
        'quantity',
        'returned_quantity',
        'total_amount',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'total_amount' => 'float',
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnDetails(): HasMany
    {
        return $this->hasMany(ReturnDetail::class);
    }

    /**
     * Eligible quantity for return: sold quantity minus already returned quantity.
     */
    public function getEligibleReturnQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->returned_quantity);
    }
}
