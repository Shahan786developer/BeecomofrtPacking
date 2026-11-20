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
        'user_id',
        'invoice_no',
        'bill_type',
        'customer_id',
        'invoice_date',
        'subtotal',
        'discount',
        'total_amount',
        'show_previous_balance',
        'previous_balance',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
        'show_previous_balance' => 'boolean',
        'previous_balance' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
        $prefix = ($billType === 'china') ? 'CH-SIN-' : 'BC-SIN-';
        $invoices = static::where('invoice_no', 'like', $prefix . '%')
            ->pluck('invoice_no');

        $maxNum = 0;
        foreach ($invoices as $inv) {
            $numPart = substr($inv, strlen($prefix));
            if (is_numeric($numPart)) {
                $n = (int) $numPart;
                if ($n > $maxNum) {
                    $maxNum = $n;
                }
            }
        }

        $nextNum = $maxNum + 1;
        $formattedNum = str_pad((string) $nextNum, max(2, strlen((string) $nextNum)), '0', STR_PAD_LEFT);

        return $prefix . $formattedNum;
    }
}
