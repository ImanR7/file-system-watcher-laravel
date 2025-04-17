<?php

namespace App\Services\FileWatcher\Watchers;

use App\Enums\SupportedEvents;
use App\Enums\SupportedExtensions;
use App\Exceptions\WatcherErrorException;
use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Illuminate\Support\Facades\Http;
use SplFileInfo;

class TxtFileWatcher implements FileWatcherInterface
{
    protected const API_URL = 'https://baconipsum.com/api/?type=meat-and-filler&paras=1&format=text';

    protected string $watchableExtensions = SupportedExtensions::TXT->value;
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
            $path = $file->getRealPath();

            if ($this->isAlreadyProcessed($path)) {
                return;
            }

            $baconText = $this->fetchBaconText();

            if ($baconText) {
                $this->appendGeneratedText($path, $baconText);
            }
        } catch (\Throwable $exception) {
            $wrapped = new WatcherErrorException($this->watchableExtensions, $exception->getMessage());
            logger()->error($wrapped->getMessage());
        }
    }

    private function isAlreadyProcessed(string $path): bool
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return !empty($lines) && trim(end($lines)) === '<!-- GENERATED_BY_TXT_WATCHER -->';
    }

    private function fetchBaconText(): ?string
    {
        $response = Http::get(self::API_URL);
        return $response->ok() ? trim($response->body()) : null;
    }

    private function appendGeneratedText(string $path, string $text): void
    {
        $formatted = "\n\n" . $text . "\n<!-- GENERATED_BY_TXT_WATCHER -->";
        file_put_contents($path, $formatted, FILE_APPEND);
    }
}
