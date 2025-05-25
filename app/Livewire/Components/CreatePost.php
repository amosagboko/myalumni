<?php

namespace App\Livewire\Components;

use App\Models\Post;
use Livewire\Component;
use App\Models\PostMedia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CreatePost extends Component
{
    use WithFileUploads;

    public $content = '';
    public $images = [];
    public $uploadProgress = 0;
    public $isUploading = false;

    protected $rules = [
        'content' => 'required|min:1',
        'images.*' => 'nullable|image|max:10240', // 10MB max
    ];

    protected $messages = [
        'images.*.max' => 'The image file size must not exceed 10MB.',
        'images.*.image' => 'Only image files are allowed.',
    ];

    public function createPost()
    {
        $this->validate();
        $this->isUploading = true;

        try {
            DB::beginTransaction();

            // Create the post
            $post = Post::create([
                'uuid' => Str::uuid(),
                'user_id' => Auth::id(),
                'content' => $this->content,
                'status' => 'published',
                'likes' => 0,
                'comments' => 0,
            ]);

            // Handle images
            if ($this->images) {
                foreach ($this->images as $image) {
                    $path = $image->store('post-images', 'public');
                    PostMedia::create([
                        'post_id' => $post->id,
                        'filetype' => 'image',
                        'file' => json_encode(['media_path' => $path, 'media_type' => 'image']),
                    ]);
                    $this->uploadProgress += 20;
                }
            }

            DB::commit();

            // Reset form
            $this->reset(['content', 'images', 'uploadProgress', 'isUploading']);
            $this->dispatch('post-created');
            session()->flash('success', 'Post created successfully!');
            
            // Log success
            Log::info('Post created successfully', ['post_id' => $post->id, 'user_id' => Auth::id()]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->isUploading = false;
            session()->flash('error', 'Failed to create post: ' . $e->getMessage());
            
            // Log error
            Log::error('Failed to create post', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
        }
    }

    public function removeImage($index)
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function render()
    {
        return view('livewire.components.create-post');
    }
}
