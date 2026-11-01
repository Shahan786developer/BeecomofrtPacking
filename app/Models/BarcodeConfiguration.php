<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarcodeConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'label_width_mm',
        'label_height_mm',
        'barcode_type',
        'font_size',
        'font_family',
        'text_align',
        'rotation',
        'auto_resize',
        'default_description_id',
    ];

    protected $casts = [
        'label_width_mm' => 'float',
        'label_height_mm' => 'float',
        'font_size' => 'integer',
        'rotation' => 'integer',
        'auto_resize' => 'boolean',
    ];

    public function defaultDescription(): BelongsTo
    {
        return $this->belongsTo(BarcodeDescription::class, 'default_description_id');
    }

    public static function getActive(): self
    {
        return static::firstOrCreate([], [
            'brand_name' => 'BEE COMFORT',
            'label_width_mm' => 32.00,
            'label_height_mm' => 22.00,
            'barcode_type' => 'Code39',
            'font_size' => 8,
            'font_family' => 'Arabic Transparent',
            'text_align' => 'center',
            'rotation' => 0,
            'auto_resize' => true,
        ]);
    }
}
