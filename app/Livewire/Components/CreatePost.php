<?php

namespace App\Livewire\Components;

use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * @deprecated Use App\Livewire\Social\PostComposer on alumni social pages.
 */
class CreatePost extends Component
{
    use WithFileUploads;

    public $content = '';
    public $images = [];
    public $uploadProgress = 0;
    public $isUploading = false;

    protected $rules = [
        'content' => 'required|min:1',
        'images.*' => 'nullable|image|max:10240',
    ];

    public function createPost(PostService $postService): void
    {
        $this->validate();
        $this->isUploading = true;

        try {
            $postService->createPost(
                Auth::user(),
                $this->content,
                FeedService::VISIBILITY_CONNECTIONS,
                $this->images ?? []
            );

            $this->reset(['content', 'images', 'uploadProgress', 'isUploading']);
            $this->dispatch('post-created');
            session()->flash('success', 'Post created successfully!');
        } catch (\Exception $e) {
            $this->isUploading = false;
            session()->flash('error', 'Failed to create post: ' . $e->getMessage());
        }
    }

    public function removeImage($index): void
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function render()
    {
        return view('livewire.components.create-post');
    }
}
