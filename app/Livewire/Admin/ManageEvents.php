<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.alumni-relations-officer')]
class ManageEvents extends Component
{

    public $eventname, $date, $venue;
    public $events;

    protected $listeners = ['refreshEvents' => 'render'];

    public function mount()
    {
        $this->events = Event::all();
    }

    public function createEvent()
    {
        $this->validate([
            'eventname' => 'required|string|max:255',
            'date' => 'required|date',
            'venue' => 'required|string|max:255',
        ]);

        Event::create([
            'eventname' => $this->eventname,
            'date' => $this->date,
            'venue' => $this->venue,
            'user_id' => Auth::id(),
        ]);

        // Reset fields
        $this->reset(['eventname', 'date', 'venue']);

        // Dispatch event to close modal
        //$this->dispatchBrowserEvent('close-modal');
        toastr()->success('Event created successfully!.');
        

        // Refresh events list
        $this->events = Event::all();
    }


    public function deleteEvent($eventId)
    {
        Event::find($eventId)->delete();
        $this->events = Event::all();
        toastr()->success('Event deleted successfully!');
        
    }

    
    public function render()
    {
        return view('livewire.admin.manage-events');
    }
}
