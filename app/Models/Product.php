<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_no',
        'parent_category',
        'category_id',
        'dozen_id',
        'product_code',
        'sku_id',
        'sku_code',
        'actual_price',
        'pricing_rule_id',
        'selling_price',
        'stock_quantity',
        'zero_stock_date',
    ];

    protected $casts = [
        'actual_price' => 'float',
        'selling_price' => 'float',
        'stock_quantity' => 'integer',
        'zero_stock_date' => 'datetime',
    ];

    protected $appends = [
        'title',
    ];

    /**
     * Category, Dozens and Product Code must always appear together
     * as one product title throughout the entire application.
     * Example: Comfort Fit (12 pieces) AA-01
     */
    public function getTitleAttribute(): string
    {
        $categoryName = $this->category ? $this->category->name : 'Category';
        $dozenLabel = $this->dozen ? $this->dozen->label : '12 pieces';
        $code = $this->product_code ?? '';

        return "{$categoryName} ({$dozenLabel}) {$code}";
    }

    public function setProductCodeAttribute($value): void
    {
        $this->attributes['product_code'] = strtoupper(trim((string) $value));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dozen(): BelongsTo
    {
        return $this->belongsTo(Dozen::class);
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function returnDetails(): HasMany
    {
        return $this->hasMany(ReturnDetail::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    /**
     * Check if product has been at zero or negative stock for 1 month (30+ days).
     */
    public function isDepletedForOneMonth(): bool
    {
        if ($this->stock_quantity > 0 || is_null($this->zero_stock_date)) {
            return false;
        }

        return Carbon::parse($this->zero_stock_date)->lte(now()->subMonth());
    }

    /**
     * Get days elapsed since zero/negative stock.
     */
    public function getDepletedDaysAttribute(): int
    {
        if ($this->stock_quantity > 0 || is_null($this->zero_stock_date)) {
            return 0;
        }

        return (int) Carbon::parse($this->zero_stock_date)->diffInDays(now());
    }

    /**
     * Atomically adjust stock and maintain movement history.
     */
    public function recordStockChange(int $quantityChange, string $type, ?int $referenceId = null, ?string $referenceType = null, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($quantityChange, $type, $referenceId, $referenceType, $notes) {
            $prevStock = $this->stock_quantity;
            $newStock = $prevStock + $quantityChange;

            $this->stock_quantity = $newStock;

            // Zero stock tracking
            if ($newStock <= 0) {
                // If not already set, record when stock first reached zero/negative
                if (is_null($this->zero_stock_date)) {
                    $this->zero_stock_date = now();
                }
            } else {
                // Recovered back to positive stock
                $this->zero_stock_date = null;
            }

            $this->save();

            return StockMovement::create([
                'product_id' => $this->id,
                'type' => $type,
                'quantity_change' => $quantityChange,
                'previous_stock' => $prevStock,
                'new_stock' => $newStock,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'notes' => $notes,
            ]);
        });
    }
}
