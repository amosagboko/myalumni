<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageEvents extends Component
{
    use WithFileUploads;

    public $eventname;

    public $date;

    public $venue;

    public $type = 'event';

    public $description;

    public $image;

    public $link;

    public $is_published = true;

    public $order;

    public $events;

    public $filterType = 'all';

    public ?string $modalMode = null;

    public ?int $selectedEventId = null;

    public ?string $existingImagePath = null;

    protected $listeners = ['refreshEvents' => 'render'];

    public function mount(): void
    {
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        $query = Event::query();

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        $this->events = $query->ordered()->get();
    }

    public function updatedFilterType(): void
    {
        $this->loadEvents();
    }

    public function showDetails(int $eventId): void
    {
        $this->fillFormFromEvent(Event::findOrFail($eventId));
        $this->modalMode = 'view';
    }

    public function openEditor(?int $eventId = null): void
    {
        if ($eventId !== null) {
            $this->fillFormFromEvent(Event::findOrFail($eventId));
        }

        $this->modalMode = 'edit';
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
    }

    public function closeModal(): void
    {
        $this->modalMode = null;
        $this->resetForm();
    }

    public function createEvent(): void
    {
        $validated = $this->validate($this->rules());
        unset($validated['image']);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('events', 'public');
        }

        Event::create([
            ...$validated,
            'image' => $imagePath,
            'is_published' => $this->is_published ?? true,
            'user_id' => Auth::id(),
        ]);

        toastr()->success('Content created successfully!');

        $this->closeModal();
        $this->loadEvents();
    }

    public function updateEvent(): void
    {
        $event = Event::findOrFail($this->selectedEventId);

        $validated = $this->validate($this->rules());
        unset($validated['image']);

        $imagePath = $event->image;
        if ($this->image) {
            if ($event->image && Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }
            $imagePath = $this->image->store('events', 'public');
        }

        $event->update([
            ...$validated,
            'image' => $imagePath,
            'is_published' => $this->is_published ?? false,
        ]);

        toastr()->success('Content updated successfully!');

        $this->closeModal();
        $this->loadEvents();
    }

    public function deleteEvent(int $eventId): void
    {
        $event = Event::findOrFail($eventId);

        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();
        $this->loadEvents();
        toastr()->success('Content deleted successfully!');
    }

    public function render()
    {
        $stats = [
            'total' => Event::count(),
            'published' => Event::where('is_published', true)->count(),
            'connect' => Event::where('type', 'connect')->count(),
            'news' => Event::where('type', 'event')->count(),
            'events' => Event::where('type', 'opportunity')->count(),
        ];

        $selectedEvent = $this->selectedEventId
            ? Event::find($this->selectedEventId)
            : null;

        return view('livewire.admin.manage-events', compact('stats', 'selectedEvent'));
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:connect,event,opportunity',
            'eventname' => 'required|string|max:255',
            'date' => 'nullable|date',
            'venue' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'eventname',
            'date',
            'venue',
            'description',
            'image',
            'link',
            'order',
            'selectedEventId',
            'existingImagePath',
        ]);
        $this->type = 'event';
        $this->is_published = true;
        $this->resetValidation();
    }

    protected function fillFormFromEvent(Event $event): void
    {
        $this->selectedEventId = $event->id;
        $this->type = $event->type;
        $this->eventname = $event->eventname;
        $this->date = $event->date?->format('Y-m-d');
        $this->venue = $event->venue;
        $this->description = $event->description;
        $this->link = $event->link;
        $this->is_published = $event->is_published;
        $this->order = $event->order;
        $this->existingImagePath = $event->image;
        $this->image = null;
        $this->resetValidation();
    }

    public function typeLabel(?string $type = null): string
    {
        return match ($type ?? $this->type) {
            'connect' => 'Highlights',
            'event' => 'News',
            'opportunity' => 'Events',
            default => 'Unknown',
        };
    }
}
