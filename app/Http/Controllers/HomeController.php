<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //

    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->hasRole('administrator')) {
                return view('admin.dashboard');
            } elseif ($user->hasRole('alumni-relations-officer')) {
                return view('aro.dashboard');
            } elseif ($user->hasRole('alumni')) {
                return view('alumni.home');
            }
        }
        
        return view('welcome');
    }
}
