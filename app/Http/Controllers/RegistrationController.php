<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterPlayerRequest;
use App\Services\RegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

readonly class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService,
    ) {}

    public function create(): View
    {
        return view('register');
    }

    public function store(RegisterPlayerRequest $request): RedirectResponse
    {
        $link = $this->registrationService->register(
            $request->validated('username'),
            $request->validated('phone'),
        );

        return redirect()
            ->route('link.show', $link)
            ->with('link_created', true);
    }
}
