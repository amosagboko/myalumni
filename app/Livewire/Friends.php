<?php

namespace App\Livewire;

use Livewire\Component;

class Friends extends Component
{

    public function makeNewFriends (){
        return view('livewire.friends');
    }

    public function render()
    {
        return view('livewire.friends');
    }
}
