<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'bill_type',
        'customer_id',
        'invoice_date',
        'subtotal',
        'discount',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }

    public static function generateNextInvoiceNo(string $billType = 'bee_comfort'): string
    {
        $prefixTag = ($billType === 'china') ? 'CH-INV-' : 'BC-INV-';
        $prefix = $prefixTag . date('Ymd') . '-';
        $latest = static::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_no');

        if ($latest) {
            $num = (int) substr($latest, strrpos($latest, '-') + 1);
            $next = str_pad((string)($num + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $prefix . $next;
    }
}
