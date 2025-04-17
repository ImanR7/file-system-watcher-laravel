<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use SplFileInfo;
use Illuminate\Support\Facades\Http;

class AntiDeleteMemeWatcher implements FileWatcherInterface
{
    protected const API_URL = 'https://meme-api.com/gimme';

    public function supports(SplFileInfo $file, string $event): bool
    {
        return $event === 'deleted';
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        $memeUrl = $this->fetchMemeImageUrl();

        if (!$memeUrl) {
            logger()->error("Failed to fetch meme from API.");
            return;
        }

        $imageContent = $this->downloadImage($memeUrl);

        if (!$imageContent) {
            logger()->error("Failed to download meme image.");
            return;
        }

        $replacementPath = $this->buildReplacementPath($file);

        $this->ensureDirectoryExists(dirname($replacementPath));

        $this->replaceWithMeme($replacementPath, $imageContent);
    }

    private function fetchMemeImageUrl(): ?string
    {
        $response = Http::get(self::API_URL);
        return $response->successful() ? $response->json()['url'] ?? null : null;
    }

    private function downloadImage(string $url): ?string
    {
        $content = @file_get_contents($url);
        return $content !== false ? $content : null;
    }

    private function buildReplacementPath(SplFileInfo $file): string
    {
        $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME) . '.jpg';
        return dirname($file->getRealPath()) . '/' . $filename;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    private function replaceWithMeme(string $path, string $content): void
    {
        file_put_contents($path, $content);
        logger()->info("File replaced with meme: " . $path);
    }
}
