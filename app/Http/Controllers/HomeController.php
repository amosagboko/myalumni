<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->hasRole('administrator')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('alumni-relations-officer')) {
                return redirect()->route('alumni-relations-officer.home');
            }

            if ($user->hasRole('alumni')) {
                return redirect()->route('alumni.home');
            }
        }

        return view('welcome');
    }
}
