<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $counter = 0;
    public function increment(){
        $this->counter++;
    }




    public function render()
    {
        return view('livewire.counter');
    }
}
