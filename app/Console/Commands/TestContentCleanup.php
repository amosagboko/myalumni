<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestContentCleanup extends Command
{
    protected $signature = 'content:test-cleanup {--days=30 : Number of days to check}';
    protected $description = 'Test content cleanup by showing what would be deleted without actually deleting';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("=== CONTENT CLEANUP TEST MODE ===");
        $this->info("Checking for content older than {$days} days...");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        $this->info("Current time: " . now()->format('Y-m-d H:i:s'));
        $this->info("");
        
        // Check Posts
        $oldPosts = Post::olderThan($days)->with('user:id,name')->get();
        $this->info("📊 POSTS ANALYSIS:");
        $this->info("• Total posts in system: " . Post::count());
        $this->info("• Posts older than {$days} days: " . $oldPosts->count());
        
        if ($oldPosts->isNotEmpty()) {
            $this->info("");
            $this->info("📋 OLD POSTS THAT WOULD BE DELETED:");
            $oldPosts->take(5)->each(function ($post) use ($days) {
                $age = $post->created_at->diffInDays(now());
                $this->line("• [{$age} days old] By: {$post->user->name}");
                $this->line("  Content: " . Str::limit($post->content, 50));
                $this->line("  Created: {$post->created_at->format('Y-m-d H:i:s')}");
                $this->line("");
            });
            
            if ($oldPosts->count() > 5) {
                $this->info("... and " . ($oldPosts->count() - 5) . " more posts");
            }
        }
        
        // Check Comments
        $oldComments = Comment::olderThan($days)->with('user:id,name')->get();
        $this->info("📊 COMMENTS ANALYSIS:");
        $this->info("• Total comments in system: " . Comment::count());
        $this->info("• Comments older than {$days} days: " . $oldComments->count());
        
        if ($oldComments->isNotEmpty()) {
            $this->info("");
            $this->info("📋 OLD COMMENTS THAT WOULD BE DELETED:");
            $oldComments->take(5)->each(function ($comment) use ($days) {
                $age = $comment->created_at->diffInDays(now());
                $this->line("• [{$age} days old] By: {$comment->user->name}");
                $this->line("  Content: " . Str::limit($comment->comment, 50));
                $this->line("  Created: {$comment->created_at->format('Y-m-d H:i:s')}");
                $this->line("");
            });
            
            if ($oldComments->count() > 5) {
                $this->info("... and " . ($oldComments->count() - 5) . " more comments");
            }
        }
        
        $this->info("");
        $this->info("🔒 SAFETY CHECK:");
        $this->info("• This is TEST MODE - no content was actually deleted");
        $this->info("• To perform actual cleanup, run: php artisan content:cleanup");
        $this->info("• To check scheduled tasks: php artisan schedule:list");
        
        return 0;
    }
} 