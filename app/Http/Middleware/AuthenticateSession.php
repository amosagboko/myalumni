<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\AuthenticateSession as Middleware;
use Illuminate\Http\Request;

class AuthenticateSession extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
        return null;
    }
} 