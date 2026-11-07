<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'article_name',
        'cup',
        'color',
        'size_32',
        'size_34',
        'size_36',
        'size_38',
        'size_40',
        'size_42',
        'size_44',
        'size_46',
        'size_48',
        'size_50',
        'rate_std',
        'rate_plus',
        'total_qty',
        'line_total',
    ];

    protected $casts = [
        'size_32' => 'integer',
        'size_34' => 'integer',
        'size_36' => 'integer',
        'size_38' => 'integer',
        'size_40' => 'integer',
        'size_42' => 'integer',
        'size_44' => 'integer',
        'size_46' => 'integer',
        'size_48' => 'integer',
        'size_50' => 'integer',
        'rate_std' => 'decimal:2',
        'rate_plus' => 'decimal:2',
        'total_qty' => 'integer',
        'line_total' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (SaleOrderItem $item) {
            $item->calculateComputedValues();
        });
    }

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    /**
     * Quantity sum for standard sizes 32 to 42
     */
    public function getStandardQtyAttribute(): int
    {
        return (int) (
            $this->size_32 +
            $this->size_34 +
            $this->size_36 +
            $this->size_38 +
            $this->size_40 +
            $this->size_42
        );
    }

    /**
     * Quantity sum for plus sizes 44 to 50
     */
    public function getPlusQtyAttribute(): int
    {
        return (int) (
            $this->size_44 +
            $this->size_46 +
            $this->size_48 +
            $this->size_50
        );
    }

    /**
     * Auto-calculate total_qty and line_total
     */
    public function calculateComputedValues(): void
    {
        $stdQty = (int) ($this->size_32 + $this->size_34 + $this->size_36 + $this->size_38 + $this->size_40 + $this->size_42);
        $plusQty = (int) ($this->size_44 + $this->size_46 + $this->size_48 + $this->size_50);

        $this->total_qty = $stdQty + $plusQty;

        $stdRate = (float) ($this->rate_std ?? 0);
        $plusRate = (float) ($this->rate_plus ?? 0);

        $this->line_total = ($stdQty * $stdRate) + ($plusQty * $plusRate);
    }
}
