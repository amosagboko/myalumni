<?php

namespace App\Http\Controllers\AlumniPresident;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('alumni-president.dashboard');
    }
}
