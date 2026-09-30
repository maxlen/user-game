<?php

namespace App\Repositories;

use App\Models\AccessLink;
use App\Models\Player;
use App\Models\Spin;
use App\Support\GameResult;
use Illuminate\Database\Eloquent\Collection;

class SpinRepository
{
    public function create(Player $player, AccessLink $link, GameResult $result): Spin
    {
        return Spin::create([
            'player_id' => $player->id,
            'access_link_id' => $link->id,
            'number' => $result->number,
            'is_win' => $result->isWin,
            'amount' => $result->amount,
        ]);
    }

    /**
     * @return Collection<int, Spin>
     */
    public function lastForPlayer(Player $player, int $limit): Collection
    {
        return Spin::where('player_id', $player->id)
            ->recent()
            ->limit($limit)
            ->get();
    }
}
