<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'customer_id',
        'order_date',
        'status',
        'total_quantity',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_quantity' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    /**
     * Generate the next sequential order number (e.g. #181, #182)
     */
    public static function generateNextOrderNo(): string
    {
        $lastOrder = static::orderByDesc('id')->first();
        if (!$lastOrder) {
            return '#181';
        }

        // Extract numbers from order_no if prefixed with #
        if (preg_match('/(\d+)/', $lastOrder->order_no, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
            return '#' . $nextNum;
        }

        return '#' . ($lastOrder->id + 180);
    }

    /**
     * Recalculate totals from items
     */
    public function recalculateTotals(): void
    {
        $this->total_quantity = (int) $this->items()->sum('total_qty');
        $this->total_amount = (float) $this->items()->sum('line_total');
        $this->save();
    }

    /**
     * Human readable badge for status
     */
    public function getStatusBadgeAttribute(): array
    {
        return match (strtolower($this->status)) {
            'completed' => [
                'class' => 'bg-success-subtle text-success border border-success-subtle',
                'label' => 'Completed',
                'icon' => 'bi-check-circle-fill',
            ],
            'rejected' => [
                'class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'label' => 'Rejected',
                'icon' => 'bi-x-circle-fill',
            ],
            default => [
                'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                'label' => 'Pending',
                'icon' => 'bi-clock-history',
            ],
        };
    }
}
