<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CardRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'contract_id',
        'product_id',
        'reason',
        'quantity',
        'notes',
        'admin_note',
        'status',
    ];

    /**
     * Client that owns the request.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Contract tied to the request.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientContract::class, 'contract_id');
    }

    /**
     * Product being requested.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Shipment generated when request is approved.
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class, 'card_request_id');
    }
}
