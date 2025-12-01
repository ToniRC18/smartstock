<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'client_id',
        'product_id',
        'card_limit_amount',
        'card_current_amount',
        'card_inactive_amount',
        'card_expired_amount',
    ];

    /**
     * Client owning the contract.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Product associated to the contract.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Per-product allocations inside this contract.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(ContractAllocation::class, 'client_contract_id');
    }

    /**
     * Available cards based on contract limit.
     */
    public function availableByLimit(): int
    {
        return max(0, (int) $this->card_limit_amount - (int) $this->card_current_amount);
    }

    /**
     * Cards available exclusively for nuevas solicitudes (excluye reemplazos por vencimiento).
     */
    public function availableForNewAssignments(): int
    {
        $available = $this->availableByLimit() - (int) $this->card_expired_amount;
        return max(0, $available);
    }

    /**
     * Ratio of inactive cards to the total assigned (current + inactive).
     */
    public function inactiveRatio(): float
    {
        $total = (int) $this->card_current_amount + (int) $this->card_inactive_amount;

        return $total > 0 ? $this->card_inactive_amount / $total : 0.0;
    }
}
