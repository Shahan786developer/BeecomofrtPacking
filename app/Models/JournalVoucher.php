<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'customer_id',
        'user_id',
        'voucher_date',
        'type',
        'amount',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate next sequential voucher number: JV-001, JV-002, ...
     */
    public static function generateNextVoucherNo(): string
    {
        $prefix = 'JV-';
        $vouchers = static::where('voucher_no', 'like', $prefix . '%')->pluck('voucher_no');

        $maxNum = 0;
        foreach ($vouchers as $v) {
            $numPart = substr($v, strlen($prefix));
            if (is_numeric($numPart)) {
                $n = (int) $numPart;
                if ($n > $maxNum) {
                    $maxNum = $n;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }
}
