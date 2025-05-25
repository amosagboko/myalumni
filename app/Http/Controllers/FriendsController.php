<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FriendsController extends Controller
{
    //

    public function accept(){
        return view('myfriends');
    }
}
