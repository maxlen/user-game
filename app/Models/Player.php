<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    /** @use HasFactory<\Database\Factories\PlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'username',
        'phone',
    ];

    /**
     * @return HasMany<AccessLink, $this>
     */
    public function accessLinks(): HasMany
    {
        return $this->hasMany(AccessLink::class);
    }

    /**
     * @return HasMany<Spin, $this>
     */
    public function spins(): HasMany
    {
        return $this->hasMany(Spin::class);
    }
}
