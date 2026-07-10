<?php

namespace App\Livewire\Social;

use App\Models\Event;
use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostComposer extends Component
{
    use WithFileUploads;

    public string $content = '';
    public string $visibility = FeedService::VISIBILITY_CONNECTIONS;
    public $images = [];
    public $videos = [];
    public ?int $sharedEventId = null;
    public bool $isUploading = false;
    public int $uploadProgress = 0;

    protected function rules(): array
    {
        $maxImages = (int) config('social.post_images.max_count', 10);
        $maxUploadKb = (int) config('social.post_images.max_upload_kb', 10240);

        return [
            'content' => 'required_without:sharedEventId|string|max:5000',
            'visibility' => 'required|in:connections,all_alumni',
            'images' => 'nullable|array|max:'.$maxImages,
            'images.*' => 'nullable|image|max:'.$maxUploadKb,
            'videos.*' => 'nullable|mimes:mp4,mov,avi|max:51200',
            'sharedEventId' => 'nullable|exists:events,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'content.required_without' => 'Please add a message or share an official event.',
            'images.*.max' => 'Each image must not exceed 10MB.',
            'images.max' => 'You can attach up to :max images per post.',
            'videos.*.max' => 'Each video must not exceed 50MB.',
        ];
    }

    public function updatedImages(): void
    {
        $maxImages = (int) config('social.post_images.max_count', 10);

        if (count($this->images) > $maxImages) {
            $this->images = array_slice($this->images, 0, $maxImages);
        }
    }

    public function createPost(PostService $postService): void
    {
        if (blank($this->sharedEventId)) {
            $this->sharedEventId = null;
        }

        $this->validate();
        $this->isUploading = true;

        try {
            $postService->createPost(
                Auth::user(),
                $this->content ?: ($this->sharedEventId ? 'Shared an official event.' : ''),
                $this->visibility,
                $this->images ?? [],
                $this->videos ?? [],
                $this->sharedEventId
            );

            $this->reset(['content', 'images', 'videos', 'sharedEventId', 'uploadProgress']);
            $this->visibility = FeedService::VISIBILITY_CONNECTIONS;
            $this->isUploading = false;

            $this->dispatch('post-created');
        } catch (\Throwable $e) {
            $this->isUploading = false;
            session()->flash('error', 'Failed to create post: ' . $e->getMessage());
        }
    }

    public function render(FeedService $feedService)
    {
        $shareableEvents = collect();

        try {
            $shareableEvents = Event::published()
                ->ordered()
                ->where('date', '>=', now()->toDateString())
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        return view('livewire.social.post-composer', [
            'visibilityOptions' => $feedService->visibilityOptions(),
            'shareableEvents' => $shareableEvents,
            'supportsVisibility' => $feedService->supportsVisibility(),
        ]);
    }
}
