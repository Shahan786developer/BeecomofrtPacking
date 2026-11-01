<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRecord extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_no',
        'sale_id',
        'customer_id',
        'return_date',
        'total_amount',
        'reason',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReturnDetail::class, 'return_id');
    }

    public static function generateNextReturnNo(): string
    {
        $prefix = 'RET-' . date('Ymd') . '-';
        $latest = static::where('return_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('return_no');

        if ($latest) {
            $num = (int) substr($latest, strrpos($latest, '-') + 1);
            $next = str_pad((string)($num + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $prefix . $next;
    }
}
