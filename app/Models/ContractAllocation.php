<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_contract_id',
        'product_id',
        'card_limit_amount',
        'card_current_amount',
        'card_inactive_amount',
        'card_expired_amount',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientContract::class, 'client_contract_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
