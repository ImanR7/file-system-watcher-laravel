<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Illuminate\Support\Facades\Http;
use SplFileInfo;

class TxtFileWatcher implements FileWatcherInterface
{
    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === 'txt' && in_array($event, ['created', 'modified']);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        try {
            $path = $file->getRealPath();
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!empty($lines) && trim(end($lines)) === '<!-- GENERATED_BY_TXT_WATCHER -->') {
                return;
            }

            $response = Http::get('https://baconipsum.com/api/?type=meat-and-filler&paras=1&format=text');

            if ($response->ok()) {
                $generatedText = "\n\n" . trim($response->body());
                $generatedText .= "\n<!-- GENERATED_BY_TXT_WATCHER -->";

                file_put_contents($path, $generatedText, FILE_APPEND);
            }
        } catch (\Exception $e) {
            logger()->error("TXT Watcher error: " . $e->getMessage());
        }
    }
}
