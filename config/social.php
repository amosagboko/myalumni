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
    'poll_interval_seconds' => (int) env('SOCIAL_POLL_INTERVAL_SECONDS', 30),

    /*
    |--------------------------------------------------------------------------
    | Connections / friend-request poll interval (seconds)
    |--------------------------------------------------------------------------
    |
    | Background poll for /friends, member profiles, and connection widgets.
    | Set to 0 to disable polling on those components.
    |
    */
    'connections_poll_interval_seconds' => (int) env('SOCIAL_CONNECTIONS_POLL_INTERVAL_SECONDS', 60),

    /*
    |--------------------------------------------------------------------------
    | Official events teasers (alumni home)
    |--------------------------------------------------------------------------
    |
    | Home feed widgets only show a subset of upcoming published events.
    | The full list is always on /alumni/events ("See all").
    |
    */
    'events_sidebar_teaser_limit' => (int) env('SOCIAL_EVENTS_SIDEBAR_TEASER_LIMIT', 3),
    'events_announcements_strip_limit' => (int) env('SOCIAL_EVENTS_ANNOUNCEMENTS_STRIP_LIMIT', 3),
    'discover_per_page' => (int) env('SOCIAL_DISCOVER_PER_PAGE', 6),

    /*
    |
    | Maximum depth for threaded replies (top-level comment = depth 1).
    | Visual indent in the UI is capped separately so deep threads stay readable.
    |
    */
    'max_comment_nesting_depth' => (int) env('SOCIAL_MAX_COMMENT_NESTING_DEPTH', 10),
    'comment_indent_cap' => (int) env('SOCIAL_COMMENT_INDENT_CAP', 4),

    /*
    |--------------------------------------------------------------------------
    | Curated emoji picker
    |--------------------------------------------------------------------------
    |
    | A small alumni-safe set for posts, comments, and replies. Native emoji
    | characters are stored directly in the existing utf8mb4 text columns.
    |
    */
    'emoji_picker' => [
        'enabled' => (bool) env('SOCIAL_EMOJI_PICKER_ENABLED', true),
        'items' => [
            '👍', '❤️', '😊', '😂', '🎉', '👏', '🙏', '🎓',
            '🥳', '✨', '🤝', '💪', '💯', '🙌', '🤔', '😢',
            '🕯️', '🕊️', '💐', '❤️‍🩹', '📚', '🏆', '🌟', '✅',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Post image uploads
    |--------------------------------------------------------------------------
    */
    'post_images' => [
        'max_count' => (int) env('SOCIAL_POST_IMAGE_MAX_COUNT', 10),
        'max_upload_kb' => (int) env('SOCIAL_POST_IMAGE_MAX_UPLOAD_KB', 10240),
        'display_max' => (int) env('SOCIAL_POST_IMAGE_DISPLAY_MAX', 1920),
        'thumb_size' => (int) env('SOCIAL_POST_IMAGE_THUMB_SIZE', 600),
        'jpeg_quality' => (int) env('SOCIAL_POST_IMAGE_JPEG_QUALITY', 85),
        'thumb_quality' => (int) env('SOCIAL_POST_IMAGE_THUMB_QUALITY', 80),
        'grid_visible_max' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Post video uploads
    |--------------------------------------------------------------------------
    |
    | 50 MB (~1–2 min at 720p) is a practical default for feed clips without
    | overloading storage or mobile uploads. Raise via env only if needed.
    |
    */
    'post_videos' => [
        'max_count' => (int) env('SOCIAL_POST_VIDEO_MAX_COUNT', 1),
        'max_upload_kb' => (int) env('SOCIAL_POST_VIDEO_MAX_UPLOAD_KB', 51200),
        'allowed_mimes' => ['mp4', 'mov', 'quicktime', 'x-msvideo'],
    ],

];
