<?php

namespace App\Http\Controllers;

use App\Models\AccessLink;
use App\Services\AccessLinkService;
use App\Services\GameService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

readonly class AccessLinkController extends Controller
{
    public function __construct(
        private AccessLinkService $accessLinkService,
        private GameService $gameService,
    ) {}

    public function show(AccessLink $accessLink): View
    {
        return view('links.show', [
            'link' => $accessLink,
            'player' => $accessLink->player,
            'url' => route('link.show', $accessLink),
            'history' => null,
        ]);
    }

    public function history(AccessLink $accessLink): View
    {
        return view('links.show', [
            'link' => $accessLink,
            'player' => $accessLink->player,
            'url' => route('link.show', $accessLink),
            'history' => $this->gameService->history($accessLink->player),
        ]);
    }

    public function regenerate(AccessLink $accessLink): RedirectResponse
    {
        $new = $this->accessLinkService->regenerate($accessLink);

        return redirect()
            ->route('link.show', $new)
            ->with('link_regenerated', true);
    }

    public function deactivate(AccessLink $accessLink): RedirectResponse
    {
        $this->accessLinkService->deactivate($accessLink);

        return redirect()
            ->route('register.form')
            ->with('link_deactivated', true);
    }
}
