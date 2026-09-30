@extends('layouts.app')

@section('title', 'Link inactive')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="alert alert-danger" role="alert">
                <h1 class="h5">This link is no longer active</h1>
                <p class="mb-0">It has either expired or been deactivated.</p>
            </div>

            <a href="{{ route('register.form') }}" class="btn btn-primary">Register again</a>
        </div>
    </div>
@endsection
