<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldContent extends Command
{
    protected $signature = 'content:cleanup {--dry-run : Report what would be deleted without deleting}';

    protected $description = 'Remove published posts older than the configured retention period';

    public function handle(): int
    {
        $retentionDays = config('social.content_retention_days', 0);

        if ($retentionDays <= 0) {
            $this->info('Social content retention is disabled (SOCIAL_CONTENT_RETENTION_DAYS=0). Skipping cleanup.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun
            ? "Dry run: checking posts older than {$retentionDays} days..."
            : "Starting content cleanup (retention: {$retentionDays} days)...");

        try {
            $query = Post::query()
                ->where('status', 'published')
                ->olderThan($retentionDays);

            $oldPostsCount = $query->count();
            $this->info("Found {$oldPostsCount} published posts older than {$retentionDays} days");

            if ($oldPostsCount === 0) {
                $this->info('Nothing to clean up.');

                return self::SUCCESS;
            }

            if ($dryRun) {
                $this->warn("Dry run complete — {$oldPostsCount} posts (and their comments via cascade) would be deleted.");

                return self::SUCCESS;
            }

            $deletedPosts = $query->delete();

            Log::info('Content cleanup completed', [
                'posts_deleted' => $deletedPosts,
                'retention_days' => $retentionDays,
                'executed_at' => now()->toDateTimeString(),
            ]);

            $this->info("Deleted {$deletedPosts} old posts (comments removed via cascade).");
            $this->info('Content cleanup completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error during content cleanup: '.$e->getMessage());

            Log::error('Content cleanup failed', [
                'error' => $e->getMessage(),
                'retention_days' => $retentionDays,
                'executed_at' => now()->toDateTimeString(),
            ]);

            return self::FAILURE;
        }
    }
}
