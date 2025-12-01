<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'stock_current', 'stock_minimum'];

    /**
     * Contracts associated to this product.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ClientContract::class);
    }

    /**
     * Determine if the product is at or below the critical stock threshold.
     */
    public function isStockCritical(): bool
    {
        return $this->stock_current <= $this->stock_minimum;
    }
}
