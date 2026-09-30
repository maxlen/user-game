<?php

namespace App\Http\Controllers;

use App\Models\AccessLink;
use App\Services\GameService;
use Illuminate\Http\RedirectResponse;

readonly class GameController extends Controller
{
    public function __construct(
        private GameService $gameService,
    ) {}

    public function play(AccessLink $accessLink): RedirectResponse
    {
        $result = $this->gameService->play($accessLink);

        return redirect()
            ->route('link.show', $accessLink)
            ->with('spin_result', $result->toArray());
    }
}
