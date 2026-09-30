@extends('layouts.app')

@section('title', 'Your link')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            @if (session('link_created'))
                <div class="alert alert-success" role="alert">
                    Welcome, {{ $player->username }}! Here is your personal link.
                </div>
            @endif

            @if (session('link_regenerated'))
                <div class="alert alert-success" role="alert">
                    Your link has been regenerated.
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="card-title h4">Hello, {{ $player->username }}</h1>

                    <label for="link-url" class="form-label mt-3">Your personal link</label>
                    <div class="input-group mb-2">
                        <input type="text" id="link-url" class="form-control" value="{{ $url }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">Copy</button>
                    </div>
                    <p class="text-muted small mb-0">Expires at {{ $link->expires_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            @if (session()->has('spin_result'))
                @php($result = session('spin_result'))
                <div class="alert {{ $result['is_win'] ? 'alert-success' : 'alert-secondary' }}" role="alert">
                    <strong>Number: {{ $result['number'] }}</strong> &mdash;
                    @if ($result['is_win'])
                        Win! You earned <strong>{{ number_format($result['amount'], 2) }}</strong>.
                    @else
                        Lose. Better luck next time.
                    @endif
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('link.lucky', $link) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Imfeelinglucky</button>
                    </form>

                    <a href="{{ route('link.history', $link) }}" class="btn btn-outline-secondary">History</a>

                    <form method="POST" action="{{ route('link.regenerate', $link) }}"
                          onsubmit="return confirm('Regenerating will invalidate your current link. Continue?');">
                        @csrf
                        <button type="submit" class="btn btn-warning">Regenerate</button>
                    </form>

                    <form method="POST" action="{{ route('link.deactivate', $link) }}"
                          onsubmit="return confirm('Deactivating will permanently disable this link. Continue?');">
                        @csrf
                        <button type="submit" class="btn btn-danger">Deactivate</button>
                    </form>
                </div>
            </div>

            @if (! is_null($history))
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Last results</h2>

                        @if ($history->isEmpty())
                            <p class="text-muted mb-0">No spins yet.</p>
                        @else
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Number</th>
                                        <th>Result</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $spin)
                                        <tr>
                                            <td>{{ $spin->number }}</td>
                                            <td>{{ $spin->is_win ? 'Win' : 'Lose' }}</td>
                                            <td>{{ number_format($spin->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script>
        function copyLink() {
            const input = document.getElementById('link-url');
            input.select();
            navigator.clipboard?.writeText(input.value);
        }
    </script>
@endsection
