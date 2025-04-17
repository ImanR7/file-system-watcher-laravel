<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Illuminate\Support\Facades\Http;
use SplFileInfo;

class JsonFileWatcher implements FileWatcherInterface
{
    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === 'json' && in_array($event, ['created', 'modified']);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        try {
            $json = file_get_contents($file->getRealPath());

            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                logger()->warning("Invalid JSON file: " . $file->getRealPath());
                return;
            }

            Http::post('https://fswatcher.requestcatcher.com/', $data);
        } catch (\Exception $e) {
            logger()->error("JSON Watcher error: " . $e->getMessage());
        }
    }
}
