<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'phone',
        'phone_2',
        'phone_3',
        'phone_4',
        'email',
        'city',
        'address',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }

    public function cashReceipts(): HasMany
    {
        return $this->hasMany(CashReceipt::class);
    }

    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->sales()->sum('total_amount');
    }

    public function getTotalReturnedAttribute(): float
    {
        return (float) $this->returns()->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->cashReceipts()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        $due = $this->total_invoiced - ($this->total_paid + $this->total_returned);
        return round(max(0, $due), 2);
    }

    /**
     * Approximate days pending since oldest invoice
     */
    public function getPendingDaysAttribute(): int
    {
        if ($this->balance_due <= 0) {
            return 0;
        }

        $oldestInvoice = $this->sales()->orderBy('invoice_date', 'asc')->first();
        if ($oldestInvoice && $oldestInvoice->invoice_date) {
            return (int) $oldestInvoice->invoice_date->diffInDays(now());
        }

        return 0;
    }
}
