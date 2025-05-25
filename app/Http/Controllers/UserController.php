<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //




    public function indexuser(){
        $user = User::all(3);

        $user->assignRole('super-admin');
        $user->assignPermission('suspend users account');
        $user->assignPermission('create event');
        $user->assignPermission('create election');
        $user->assignPermission('moderate post or delete');
        $user->assignPermission('upload alumni');
        $user->assignPermission('create all users');
        $user->assignPermission('create fee template');
        $user->assignPermission('create post');
        $user->assignPermission('comment on post');
        $user->assignPermission('view all users');
        $user->assignPermission('view all events');
        $user->assignPermission('view alumni');
        $user->assignPermission('update profile');
        $user->assignPermission('request transcript');
        $user->assignPermission('generate report');
        $user->assignPermission('fee template');
        $user->assignPermission('chat');
        $user->assignPermission('activate alumni');
        $user->assignPermission('membership');
        $user->assignPermission('message');
        $user->assignPermission('my transactions');
        $user->assignPermission('my donations');
        $user->assignPermission('job post');

        return view('admin.users.undex', compact('users'));
    }
}
