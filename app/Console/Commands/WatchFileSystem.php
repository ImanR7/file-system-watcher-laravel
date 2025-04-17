<?php

namespace App\Console\Commands;

use App\Services\FileWatcher\WatcherManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class WatchFileSystem extends Command
{
    protected $signature = 'watch:fs';
    protected $description = 'Watch a directory for file changes (create, modify, delete)';

    protected string $watchPath;
    protected int $pollingInterval;
    protected array $previousSnapshot = [];

    protected WatcherManager $manager;


    public function handle(): void
    {
        $this->watchPath = Config::get('fswatcher.watch_path');
        $this->pollingInterval = Config::get('fswatcher.polling_interval');

        $this->info("🔍 Watching folder: {$this->watchPath}");
        $this->previousSnapshot = $this->snapshot();

        $this->manager = new WatcherManager();

        while (true) {
            sleep($this->pollingInterval);

            $currentSnapshot = $this->snapshot();
            $this->detectChanges($this->previousSnapshot, $currentSnapshot);

            $this->previousSnapshot = $currentSnapshot;
        }
    }

    protected function snapshot(): array
    {
        $files = File::allFiles($this->watchPath);
        $snapshot = [];

        foreach ($files as $file) {
            $snapshot[$file->getRealPath()] = [
                'mtime' => $file->getMTime(),
                'size'  => $file->getSize(),
            ];
        }

        return $snapshot;
    }

    protected function detectChanges(array $previous, array $current): void
    {
        // Created files
        $created = array_diff_key($current, $previous);
        foreach ($created as $path => $meta) {
            $this->log("🟢 Created: $path");
            $this->manager->handle(new SplFileInfo($path), 'created');
        }

        // Modified files
        foreach ($current as $path => $meta) {
            if (isset($previous[$path]) &&
                ($previous[$path]['mtime'] !== $meta['mtime'] || $previous[$path]['size'] !== $meta['size'])) {
                $this->log("🟡 Modified: $path");
                $this->manager->handle(new SplFileInfo($path), 'modified');
            }
        }

        // Deleted files
        $deleted = array_diff_key($previous, $current);
        foreach ($deleted as $path => $meta) {
            $this->log("🔴 Deleted: $path");
            $this->manager->handle(new SplFileInfo($path), 'deleted');
        }
    }

    protected function log(string $message): void
    {
        if (Config::get('fswatcher.log_changes', true)) {
            $this->line($message);
        }
    }
}
