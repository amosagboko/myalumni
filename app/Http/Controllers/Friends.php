<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Friends extends Controller
{
    //

    public function makeNewFriends (){
        return view('myfriends');
    }
}
