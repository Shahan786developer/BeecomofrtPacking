<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'sale_detail_id',
        'product_id',
        'product_title',
        'sku_code',
        'unit_price',
        'quantity',
        'total_amount',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'total_amount' => 'float',
        'quantity' => 'integer',
    ];

    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(ReturnRecord::class, 'return_id');
    }

    public function saleDetail(): BelongsTo
    {
        return $this->belongsTo(SaleDetail::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
