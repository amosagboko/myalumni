<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CleanupOldChats extends Command
{
    protected $signature = 'chat:cleanup {--days=30 : Number of days to keep messages}';
    protected $description = 'Remove chat messages older than specified days (default: 30 days)';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("Starting chat cleanup for messages older than {$days} days...");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        
        try {
            // Count messages that will be deleted using the scope
            $messagesToDelete = Message::olderThan($days)->count();
            
            if ($messagesToDelete === 0) {
                $this->info('No old messages found to delete.');
                return 0;
            }
            
            $this->info("Found {$messagesToDelete} messages to delete.");
            
            // Delete old messages using the scope (this will also remove soft-deleted ones)
            $deletedCount = Message::olderThan($days)->delete();
            
            // Log the cleanup activity
            Log::info('Chat cleanup completed', [
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                'messages_deleted' => $deletedCount,
                'days_old' => $days,
                'executed_at' => now()->format('Y-m-d H:i:s')
            ]);
            
            $this->info("Successfully deleted {$deletedCount} old chat messages.");
            $this->info('Chat cleanup completed successfully.');
            
            return 0;
            
        } catch (\Exception $e) {
            $errorMessage = "Error during chat cleanup: " . $e->getMessage();
            $this->error($errorMessage);
            
            // Log the error
            Log::error('Chat cleanup failed', [
                'error' => $e->getMessage(),
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                'days_old' => $days,
                'executed_at' => now()->format('Y-m-d H:i:s')
            ]);
            
            return 1;
        }
    }
} 