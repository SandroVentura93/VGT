<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'base_price',
        'sale_price',
        'profit_percentage',
        'includes_igv',
        'includes_surcharge',
        'offer_percentage',
        'offer_ends_at',
        'offer_duration_hours',
        'image',
        'images',
        'category',
        'featured',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'base_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'profit_percentage' => 'integer',
            'includes_igv' => 'boolean',
            'includes_surcharge' => 'boolean',
            'offer_percentage' => 'integer',
            'offer_ends_at' => 'datetime',
            'offer_duration_hours' => 'integer',
            'featured' => 'boolean',
            'images' => 'array',
        ];
    }

    public function isOnOffer(): bool
    {
        return $this->offer_percentage > 0 && $this->offer_ends_at?->isFuture();
    }

    public function getOriginalPriceAttribute(): ?float
    {
        if ($this->sale_price !== null) {
            return (float) $this->sale_price;
        }

        if (! $this->offer_percentage) {
            return null;
        }

        $discountFactor = 1 - ($this->offer_percentage / 100);

        if ($discountFactor <= 0) {
            return null;
        }

        return round((float) $this->price / $discountFactor, 2);
    }
}
