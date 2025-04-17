<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Illuminate\Support\Facades\Http;
use SplFileInfo;

class TxtFileWatcher implements FileWatcherInterface
{
    protected const WATCHABLE_EXTENSION = 'txt';
    protected const SUPPORTED_EVENTS = ['created', 'modified'];
    protected const API_URL = 'https://baconipsum.com/api/?type=meat-and-filler&paras=1&format=text';

    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === self::WATCHABLE_EXTENSION && in_array($event, self::SUPPORTED_EVENTS);
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
        } catch (\Exception $e) {
            logger()->error("TXT Watcher error: " . $e->getMessage());
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
