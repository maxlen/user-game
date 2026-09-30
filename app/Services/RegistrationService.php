<?php

namespace App\Services;

use App\Models\AccessLink;
use App\Repositories\PlayerRepository;
use Illuminate\Support\Facades\DB;

readonly class RegistrationService
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private AccessLinkService $accessLinkService,
    ) {}

    public function register(string $username, string $phone): AccessLink
    {
        return DB::transaction(function () use ($username, $phone) {
            $player = $this->playerRepository->create($username, $phone);

            return $this->accessLinkService->issueFor($player);
        });
    }
}
