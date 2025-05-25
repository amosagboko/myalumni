@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-7">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Results - {{ $election->title }}</h3>
                </div>
                <div class="card-body">
                    <p>This is the results page for <strong>{{ $election->title }}</strong>.</p>
                    <!-- Election results will go here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 