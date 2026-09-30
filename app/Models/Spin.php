<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spin extends Model
{
    /** @use HasFactory<\Database\Factories\SpinFactory> */
    use HasFactory;

    protected $fillable = [
        'player_id',
        'access_link_id',
        'number',
        'is_win',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'is_win' => 'bool',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return BelongsTo<AccessLink, $this>
     */
    public function accessLink(): BelongsTo
    {
        return $this->belongsTo(AccessLink::class);
    }

    /**
     * @param  Builder<Spin>  $query
     * @return Builder<Spin>
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }
}
