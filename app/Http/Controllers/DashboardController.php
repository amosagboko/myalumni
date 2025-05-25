<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('support-admin')) {
            return view('dashboard.supportadmin');
        // } elseif ($user->hasRole('manage-alumni')) {
        //     return view('dashboard.manage-alumni');
        // } elseif ($user->hasRole('user')) {
        //     return view('dashboard.user');
        }
        return view('dashboard');
        // abort(403, 'Unauthorized');
    }
}
