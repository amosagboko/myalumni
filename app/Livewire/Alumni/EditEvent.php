<?php

namespace App\Livewire\Alumni;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.alumni')]
class EditEvent extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Event $event;

    public string $eventname = '';

    public ?string $date = null;

    public ?string $venue = null;

    public ?string $description = null;

    public $image;

    public ?string $link = null;

    public ?string $existingImagePath = null;

    public function mount(Event $event): void
    {
        $this->authorize('update', $event);

        $this->event = $event;
        $this->eventname = $event->eventname;
        $this->date = $event->date?->format('Y-m-d');
        $this->venue = $event->venue;
        $this->description = $event->description;
        $this->link = $event->link;
        $this->existingImagePath = $event->image;
    }

    public function save(): void
    {
        $this->authorize('update', $this->event);

        $validated = $this->validate($this->rules());
        unset($validated['image']);

        $imagePath = $this->event->image;
        if ($this->image) {
            if ($this->event->image && Storage::disk('public')->exists($this->event->image)) {
                Storage::disk('public')->delete($this->event->image);
            }
            $imagePath = $this->image->store('events', 'public');
        }

        $this->event->update([
            ...$validated,
            'image' => $imagePath,
        ]);

        session()->flash('success', 'Event updated successfully.');

        $this->redirectRoute('alumni.events.mine', navigate: true);
    }

    public function render()
    {
        return view('livewire.alumni.edit-event');
    }

    protected function rules(): array
    {
        return [
            'eventname' => 'required|string|max:255',
            'date' => 'nullable|date',
            'venue' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|url|max:255',
        ];
    }
}
