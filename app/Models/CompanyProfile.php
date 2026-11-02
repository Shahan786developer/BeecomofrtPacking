<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tagline',
        'address',
        'phone',
        'email',
        'terms_conditions',
    ];

    /**
     * Compatibility Accessors
     */
    public function getCompanyNameAttribute(): ?string
    {
        return $this->name;
    }

    public function getWarehouseAddressAttribute(): ?string
    {
        return $this->address;
    }

    public function getPhonePrimaryAttribute(): ?string
    {
        return $this->phone;
    }

    public function getInvoiceTermsAttribute(): ?string
    {
        return $this->terms_conditions;
    }

    /**
     * Get active company profile singleton
     */
    public static function getActive(): self
    {
        return static::firstOrCreate([], [
            'name' => 'Bee Comfort',
            'tagline' => 'Packing & Retail Garment Solutions',
            'address' => 'Warehouse • Pakistan',
            'phone' => '+92 300 0000000',
            'email' => 'orders@beecomfort.com',
            'terms_conditions' => 'Thank you for your business. Please inspect goods upon receipt. Returns accepted per company policy.',
        ]);
    }
}
