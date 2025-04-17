<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Illuminate\Support\Facades\Http;
use SplFileInfo;

class JsonFileWatcher implements FileWatcherInterface
{
    protected const WATCHABLE_EXTENSION = 'json';
    protected const SUPPORTED_EVENTS = ['created', 'modified'];
    protected const API_URL = 'https://fswatcher.requestcatcher.com/';

    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === self::WATCHABLE_EXTENSION && in_array($event, self::SUPPORTED_EVENTS);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        try {
            $data = $this->parseJsonFile($file);

            if ($data === null) {
                logger()->warning("Invalid JSON file: " . $file->getRealPath());
                return;
            }

            $this->sendToWebhook($data);
        } catch (\Exception $e) {
            logger()->error("JSON Watcher error: " . $e->getMessage());
        }
    }

    private function parseJsonFile(SplFileInfo $file): ?array
    {
        $json = file_get_contents($file->getRealPath());
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    private function sendToWebhook(array $data): void
    {
        Http::post(self::API_URL, $data);
    }
}
