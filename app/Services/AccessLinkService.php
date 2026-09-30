<?php

namespace App\Services;

use App\Models\AccessLink;
use App\Models\Player;
use App\Repositories\AccessLinkRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

readonly class AccessLinkService
{
    public function __construct(
        private AccessLinkRepository $accessLinkRepository,
    ) {}

    public function issueFor(Player $player): AccessLink
    {
        do {
            $token = Str::random(64);
        } while ($this->accessLinkRepository->tokenExists($token));

        $expiresAt = now()->addDays(config('game.link_lifetime_days'));

        return $this->accessLinkRepository->create($player, $token, $expiresAt);
    }

    public function regenerate(AccessLink $link): AccessLink
    {
        return DB::transaction(function () use ($link) {
            $this->accessLinkRepository->revoke($link);

            return $this->issueFor($link->player);
        });
    }

    public function deactivate(AccessLink $link): void
    {
        $this->accessLinkRepository->revoke($link);
    }
}
