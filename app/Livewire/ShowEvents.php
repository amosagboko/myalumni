<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class ShowEvents extends Component
{

    public $events;

    public function mount()
    {
        $this->events = Event::latest()->get();
    }


    public function render()
    {
        return view('livewire.show-events');
    }
}
