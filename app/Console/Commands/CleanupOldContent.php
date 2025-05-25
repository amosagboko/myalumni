<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldContent extends Command
{
    protected $signature = 'content:cleanup';
    protected $description = 'Remove posts and comments older than 30 days';

    public function handle()
    {
        $this->info('Starting content cleanup...');
        
        // Delete old posts
        $deletedPosts = Post::olderThan(30)->delete();
        $this->info("Deleted {$deletedPosts} old posts");
        
        // Delete old comments
        $deletedComments = Comment::olderThan(30)->delete();
        $this->info("Deleted {$deletedComments} old comments");
        
        $this->info('Content cleanup completed successfully');
    }
} 