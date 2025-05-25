@extends('layouts.alumni')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Verify Your Email</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p>Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success">
                            <p>A new verification link has been sent to the email address you provided during registration.</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Resend Verification Email
                            </button>
                        </form>
                    </div>

                    <div class="mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
