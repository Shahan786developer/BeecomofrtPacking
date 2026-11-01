<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dozen extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'quantity',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'quantity' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
