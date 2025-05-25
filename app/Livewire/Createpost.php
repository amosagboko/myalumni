<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostMedia;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Createpost extends Component
{
    use WithFileUploads;

    public $content;
    public $images = [];
    public $videos = [];

    protected $rules = [
        'content' => 'required|string|max:1000',
        'images.*' => 'nullable|image|max:10240', // 10MB max
        'videos.*' => 'nullable|mimes:mp4,mov,avi|max:51200', // 50MB max
    ];

    public function createPost()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // Create the post
            $post = Post::create([
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
                        'media_path' => $path,
                        'media_type' => 'image',
                    ]);
                }
            }

            // Handle videos
            if ($this->videos) {
                foreach ($this->videos as $video) {
                    $path = $video->store('post-videos', 'public');
                    PostMedia::create([
                        'post_id' => $post->id,
                        'media_path' => $path,
                        'media_type' => 'video',
                    ]);
                }
            }

            DB::commit();

            // Reset form
            $this->reset(['content', 'images', 'videos']);
            toastr()->success('Post created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Error creating post: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.createpost');
    }
}
