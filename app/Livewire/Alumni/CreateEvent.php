<?php

namespace App\Livewire\Alumni;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.alumni')]
class CreateEvent extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $eventname = '';

    public ?string $date = null;

    public ?string $venue = null;

    public ?string $description = null;

    public $image;

    public ?string $link = null;

    public function mount(): void
    {
        $this->authorize('create', Event::class);
    }

    public function save(): void
    {
        $this->authorize('create', Event::class);

        $validated = $this->validate($this->rules());
        unset($validated['image']);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('events', 'public');
        }

        Event::create([
            ...$validated,
            'type' => 'opportunity',
            'image' => $imagePath,
            'is_published' => false,
            'user_id' => Auth::id(),
        ]);

        session()->flash('success', 'Your event was submitted for review. It will appear in Discover once approved.');

        $this->redirectRoute('alumni.events.mine', navigate: true);
    }

    public function render()
    {
        return view('livewire.alumni.create-event');
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
