<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Directory to Watch
    |--------------------------------------------------------------------------
    |
    | This is the directory that the watcher service will monitor for file
    | changes (created, modified, deleted).
    |
    */
    'watch_path' => storage_path('fswatch'),

    /*
    |--------------------------------------------------------------------------
    | Polling Interval
    |--------------------------------------------------------------------------
    |
    | The interval (in seconds) between each file system check.
    |
    */
    'polling_interval' => 1,

    /*
    |--------------------------------------------------------------------------
    | Logging Enabled
    |--------------------------------------------------------------------------
    |
    | Whether to log each change in the filesystem.
    |
    */
    'log_changes' => true,
];
