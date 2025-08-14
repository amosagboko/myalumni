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
        
        try {
            // Count old posts before deletion
            $oldPostsCount = Post::olderThan(30)->count();
            $this->info("Found {$oldPostsCount} posts older than 30 days");
            
            // Delete old posts
            $deletedPosts = Post::olderThan(30)->delete();
            $this->info("Deleted {$deletedPosts} old posts");
            
            // Count old comments before deletion
            $oldCommentsCount = Comment::olderThan(30)->count();
            $this->info("Found {$oldCommentsCount} comments older than 30 days");
            
            // Delete old comments
            $deletedComments = Comment::olderThan(30)->delete();
            $this->info("Deleted {$deletedComments} old comments");
            
            // Log the cleanup activity
            \Illuminate\Support\Facades\Log::info('Content cleanup completed', [
                'posts_deleted' => $deletedPosts,
                'comments_deleted' => $deletedComments,
                'executed_at' => now()->format('Y-m-d H:i:s')
            ]);
            
            $this->info('Content cleanup completed successfully');
            
        } catch (\Exception $e) {
            $errorMessage = "Error during content cleanup: " . $e->getMessage();
            $this->error($errorMessage);
            
            // Log the error
            \Illuminate\Support\Facades\Log::error('Content cleanup failed', [
                'error' => $e->getMessage(),
                'executed_at' => now()->format('Y-m-d H:i:s')
            ]);
            
            return 1;
        }
    }
} 