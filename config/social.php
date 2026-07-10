<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feed pagination
    |--------------------------------------------------------------------------
    */
    'feed_per_page' => (int) env('SOCIAL_FEED_PER_PAGE', 15),

    /*
    |--------------------------------------------------------------------------
    | Content retention (content:cleanup)
    |--------------------------------------------------------------------------
    |
    | Days after which published posts are removed. Set to 0 to disable
    | automatic deletion — recommended for the alumni feed.
    |
    | Comments are removed via cascade when their post is deleted; they are
    | never deleted independently while the parent post still exists.
    |
    */
    'content_retention_days' => (int) env('SOCIAL_CONTENT_RETENTION_DAYS', 0),

    /*
    |--------------------------------------------------------------------------
    | Real-time feed / notifications (Laravel Reverb + Echo)
    |--------------------------------------------------------------------------
    |
    | Keep false until Reverb is running in your environment. When false,
    | posts/likes/comments still work — only live updates are disabled.
    |
    */
    'realtime_enabled' => (bool) env('SOCIAL_REALTIME_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Background poll interval (seconds)
    |--------------------------------------------------------------------------
    |
    | When realtime is disabled, Livewire quietly polls the feed on this
    | interval while the tab is visible. Set to 0 to disable polling.
    |
    */
    'poll_interval_seconds' => (int) env('SOCIAL_POLL_INTERVAL_SECONDS', 10),

];
