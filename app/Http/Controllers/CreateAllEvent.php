<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateAllEvent extends Controller
{
    //

    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('administrator');
        $isARO = $user->hasRole('alumni-relations-officer');

        // Determine which layout to use
        if ($isAdmin) {
            return view('admin.create-events');
        } elseif ($isARO) {
            return view('aro.create-events');
        }

        // Fallback to admin view if role is not recognized
        return view('admin.create-events');
    }
}
