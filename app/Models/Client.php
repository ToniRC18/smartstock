<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name'];

    /**
     * Contracts associated to this client.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ClientContract::class);
    }
}
