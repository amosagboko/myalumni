<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.alumni-relations-officer')]
class ManageEvents extends Component
{
    use WithFileUploads;

    public $eventname, $date, $venue, $type = 'event', $description, $image, $link, $is_published = true, $order;
    public $events;
    public $filterType = 'all';

    protected $listeners = ['refreshEvents' => 'render'];

    public function mount()
    {
        $this->loadEvents();
    }

    public function loadEvents()
    {
        $query = Event::query();
        
        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }
        
        $this->events = $query->ordered()->get();
    }

    public function updatedFilterType()
    {
        $this->loadEvents();
    }

    public function createEvent()
    {
        $this->validate([
            'type' => 'required|in:connect,event,opportunity',
            'eventname' => 'required|string|max:255',
            'date' => 'nullable|date',
            'venue' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|url|max:255',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('events', 'public');
        }

        Event::create([
            'type' => $this->type,
            'eventname' => $this->eventname,
            'date' => $this->date,
            'venue' => $this->venue,
            'description' => $this->description,
            'image' => $imagePath,
            'link' => $this->link,
            'is_published' => $this->is_published ?? true,
            'order' => $this->order,
            'user_id' => Auth::id(),
        ]);

        // Reset fields
        $this->reset(['eventname', 'date', 'venue', 'type', 'description', 'image', 'link', 'is_published', 'order']);
        $this->type = 'event';
        $this->is_published = true;

        toastr()->success('Content created successfully!');

        // Refresh events list
        $this->loadEvents();
    }


    public function deleteEvent($eventId)
    {
        $event = Event::find($eventId);
        
        // Delete image if exists
        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }
        
        $event->delete();
        $this->loadEvents();
        toastr()->success('Content deleted successfully!');
    }

    
    public function render()
    {
        return view('livewire.admin.manage-events');
    }
}
