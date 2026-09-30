@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title h4 mb-4">Register</h1>

                    @if (session('link_deactivated'))
                        <div class="alert alert-info" role="alert">
                            Your link has been deactivated. Register again to get a new one.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input
                                type="text"
                                class="form-control @error('username') is-invalid @enderror"
                                id="username"
                                name="username"
                                value="{{ old('username') }}"
                                required
                            >
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phonenumber</label>
                            <input
                                type="text"
                                class="form-control @error('phone') is-invalid @enderror"
                                id="phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="+380501112233"
                                required
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
