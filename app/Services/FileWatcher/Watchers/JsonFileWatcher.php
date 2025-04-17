<?php

namespace App\Services\FileWatcher\Watchers;

use App\Enums\SupportedEvents;
use App\Enums\SupportedExtensions;
use App\Exceptions\InvalidJsonException;
use App\Exceptions\WatcherErrorException;
use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Illuminate\Support\Facades\Http;
use SplFileInfo;

class JsonFileWatcher implements FileWatcherInterface
{
    protected const API_URL = 'https://fswatcher.requestcatcher.com/';

    protected string $watchableExtensions = SupportedExtensions::JSON->value;
    protected array $watchableEvents = [
        SupportedEvents::CREATED->value,
        SupportedEvents::MODIFIED->value,
    ];

    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === $this->watchableExtensions && in_array($event, $this->watchableEvents);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        try {
            $data = $this->parseJsonFile($file);

            if ($data === null) {
                throw new InvalidJsonException($file->getRealPath());
            }

            $this->sendToWebhook($data);
        } catch (\Throwable $exception) {
            throw new WatcherErrorException($this->watchableExtensions, $exception->getMessage());
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
