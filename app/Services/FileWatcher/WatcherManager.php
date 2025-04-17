<?php

namespace App\Services\FileWatcher;

use App\Services\FileWatcher\Watchers\AntiDeleteMemeWatcher;
use App\Services\FileWatcher\Watchers\JpgFileWatcher;
use App\Services\FileWatcher\Watchers\JsonFileWatcher;
use App\Services\FileWatcher\Watchers\TxtFileWatcher;
use App\Services\FileWatcher\Watchers\ZipFileWatcher;
use SplFileInfo;

class WatcherManager
{
    protected array $watchers = [];

    public function __construct()
    {
        $this->watchers = [
            new TxtFileWatcher(),
            new JsonFileWatcher(),
            new JpgFileWatcher(),
            new ZipFileWatcher(),
            new AntiDeleteMemeWatcher(),
        ];
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        foreach ($this->watchers as $watcher) {
            if ($watcher->supports($file, $event)) {
                $watcher->handle($file, $event);
            }
        }
    }
}
