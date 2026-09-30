<?php

namespace App\Repositories;

use App\Models\AccessLink;
use App\Models\Player;
use Carbon\CarbonInterface;

class AccessLinkRepository
{
    public function findByToken(string $token): ?AccessLink
    {
        return AccessLink::where('token', $token)->first();
    }

    public function tokenExists(string $token): bool
    {
        return AccessLink::where('token', $token)->exists();
    }

    public function create(Player $player, string $token, CarbonInterface $expiresAt): AccessLink
    {
        return AccessLink::create([
            'player_id' => $player->id,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function revoke(AccessLink $link): void
    {
        $link->update(['revoked_at' => now()]);
    }
}
