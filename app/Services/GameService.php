<?php

namespace App\Services;

use App\Contracts\RandomNumberGenerator;
use App\Models\AccessLink;
use App\Models\Player;
use App\Repositories\SpinRepository;
use App\Support\GameResult;
use Illuminate\Database\Eloquent\Collection;

readonly class GameService
{
    public function __construct(
        private RandomNumberGenerator $randomNumberGenerator,
        private SpinRepository $spinsRepository,
    ) {}

    public function play(AccessLink $link): GameResult
    {
        $number = $this->randomNumberGenerator->generate(1, config('game.number_max'));

        $result = GameResult::forNumber($number);

        $this->spinsRepository->create($link->player, $link, $result);

        return $result;
    }

    /**
     * @return Collection<int, \App\Models\Spin>
     */
    public function history(Player $player): Collection
    {
        return $this->spinsRepository->lastForPlayer($player, config('game.history_size'));
    }
}
