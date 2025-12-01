<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_request_id',
        'tracking_code',
        'status',
        'eta_date',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(CardRequest::class, 'card_request_id');
    }
}
