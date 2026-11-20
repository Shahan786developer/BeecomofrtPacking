<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'owner_name',
        'phone',
        'phone_2',
        'phone_3',
        'phone_4',
        'email',
        'city',
        'address',
        'ntn',
        'strn',
        'credit_days',
    ];

    protected $casts = [
        'credit_days' => 'integer',
    ];

    public function getFormattedCodeAttribute(): string
    {
        return $this->code ?: ('B' . sprintf('%03d', $this->id));
    }

    public function getDisplayWithCodeAttribute(): string
    {
        return "{$this->name} ({$this->formatted_code})";
    }

    public function getSearchDropdownLabelAttribute(): string
    {
        $owner = $this->owner_name ? $this->owner_name : 'No Owner';
        $city = $this->city ? $this->city : 'No City';
        return "{$this->name} — Owner: {$owner} • City: {$city}";
    }

    /**
     * Live previous balance from ledger
     * (total sales + JV debits minus total returns, cash receipts, and JV credits)
     */
    public function getPreviousBalance(): float
    {
        $debits = (float) $this->sales()->sum('total_amount') + (float) $this->journalVouchers()->where('type', 'debit')->sum('amount');
        $credits = (float) $this->returns()->sum('total_amount') + (float) $this->cashReceipts()->sum('amount') + (float) $this->journalVouchers()->where('type', 'credit')->sum('amount');
        return round($debits - $credits, 2);
    }

    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class);
    }

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

    public function journalVouchers(): HasMany
    {
        return $this->hasMany(JournalVoucher::class);
    }

    public function getTotalInvoicedAttribute(): float
    {
        $sales = (float) $this->sales()->sum('total_amount');
        $jvDebits = (float) $this->journalVouchers()->where('type', 'debit')->sum('amount');
        return round($sales + $jvDebits, 2);
    }

    public function getTotalReturnedAttribute(): float
    {
        return (float) $this->returns()->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        $paid = (float) $this->cashReceipts()->sum('amount');
        $jvCredits = (float) $this->journalVouchers()->where('type', 'credit')->sum('amount');
        return round($paid + $jvCredits, 2);
    }

    public function getBalanceDueAttribute(): float
    {
        $due = $this->total_invoiced - ($this->total_paid + $this->total_returned);
        return round(max(0, $due), 2);
    }

    public function getCreditDaysLabelAttribute(): string
    {
        return $this->credit_days ? "{$this->credit_days} Days" : 'None';
    }

    /**
     * Approximate days pending since oldest invoice
     */
    public function getPendingDaysAttribute(): int
    {
        $info = $this->getOldestUnpaidInvoiceInfo();
        return $info ? $info['days_elapsed'] : 0;
    }

    /**
     * FIFO Calculation of the oldest unpaid or partially paid invoice
     */
    public function getOldestUnpaidInvoiceInfo(): ?array
    {
        $totalInvoiced = $this->total_invoiced;
        $totalSettled = $this->total_paid + $this->total_returned;
        $due = round(max(0, $totalInvoiced - $totalSettled), 2);

        if ($due <= 0) {
            return null;
        }

        $remainingSettled = $totalSettled;
        $oldestUnpaid = null;
        $unpaidPortion = 0.0;

        $sales = $this->sales()->orderBy('invoice_date', 'asc')->orderBy('id', 'asc')->get();

        foreach ($sales as $sale) {
            $saleTotal = (float) $sale->total_amount;
            if ($remainingSettled >= $saleTotal) {
                $remainingSettled -= $saleTotal;
            } else {
                $oldestUnpaid = $sale;
                $unpaidPortion = round($saleTotal - $remainingSettled, 2);
                break;
            }
        }

        if (!$oldestUnpaid) {
            // Edge case: customer has positive balance due but no specific sales in table
            $date = $this->created_at ? Carbon::parse($this->created_at)->startOfDay() : now()->startOfDay();
            $daysElapsed = (int) $date->diffInDays(now()->startOfDay());
            $creditDays = (int) ($this->credit_days ?: 0);
            $dueDate = $creditDays > 0 ? (clone $date)->addDays($creditDays) : null;
            $isOverdue = $creditDays > 0 && ($daysElapsed > $creditDays);
            $overdueDays = $isOverdue ? ($daysElapsed - $creditDays) : 0;

            return [
                'sale' => null,
                'invoice_no' => 'Opening / Prior Balance',
                'invoice_date' => $date,
                'invoice_amount' => $due,
                'unpaid_portion' => $due,
                'total_balance_due' => $due,
                'days_elapsed' => $daysElapsed,
                'credit_days' => $creditDays,
                'due_date' => $dueDate,
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
            ];
        }

        $invoiceDate = $oldestUnpaid->invoice_date ? Carbon::parse($oldestUnpaid->invoice_date)->startOfDay() : Carbon::parse($oldestUnpaid->created_at)->startOfDay();
        $daysElapsed = (int) $invoiceDate->diffInDays(now()->startOfDay());
        $creditDays = (int) ($this->credit_days ?: 0);
        $dueDate = $creditDays > 0 ? (clone $invoiceDate)->addDays($creditDays) : null;
        $isOverdue = $creditDays > 0 && ($daysElapsed > $creditDays);
        $overdueDays = $isOverdue ? ($daysElapsed - $creditDays) : 0;

        return [
            'sale' => $oldestUnpaid,
            'invoice_no' => $oldestUnpaid->invoice_no,
            'invoice_date' => $invoiceDate,
            'invoice_amount' => (float) $oldestUnpaid->total_amount,
            'unpaid_portion' => $unpaidPortion,
            'total_balance_due' => $due,
            'days_elapsed' => $daysElapsed,
            'credit_days' => $creditDays,
            'due_date' => $dueDate,
            'is_overdue' => $isOverdue,
            'overdue_days' => $overdueDays,
        ];
    }

    /**
     * Is this customer in the Default List?
     * (Has credit_days, has balance_due > 0, and days_elapsed > credit_days)
     */
    public function isDefaulter(): bool
    {
        if (empty($this->credit_days) || (int)$this->credit_days <= 0) {
            return false;
        }

        $info = $this->getOldestUnpaidInvoiceInfo();
        return $info !== null && $info['is_overdue'] === true;
    }
}

