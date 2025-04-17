<?php

namespace App\Services\FileWatcher\Watchers;

use App\Enums\SupportedEvents;
use App\Enums\SupportedExtensions;
use App\Exceptions\WatcherErrorException;
use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use SplFileInfo;
use ZipArchive;


class ZipFileWatcher implements FileWatcherInterface
{
    protected string $watchableExtensions = SupportedExtensions::ZIP->value;
    protected array $watchableEvents = [
        SupportedEvents::CREATED->value,
    ];

    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === $this->watchableExtensions && in_array($event, $this->watchableEvents);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        $path = $file->getRealPath();
        $extractPath = $this->getExtractPath($path);

        if ($this->shouldExtract($extractPath)) {
            $this->createExtractDirectory($extractPath);
            $this->extractZip($path, $extractPath);
        } else {
            logger()->info("Extract directory already exists: " . $extractPath);
        }
    }

    private function getExtractPath(string $zipPath): string
    {
        return dirname($zipPath) . '/' . pathinfo($zipPath, PATHINFO_FILENAME);
    }

    private function shouldExtract(string $extractPath): bool
    {
        return !file_exists($extractPath);
    }

    private function createExtractDirectory(string $path): void
    {
        mkdir($path, 0777, true);
    }

    private function extractZip(string $zipPath, string $extractPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            logger()->info("Zip extracted: " . $extractPath);
        } else {
            throw new WatcherErrorException($this->watchableExtensions, "Failed to open Zip file.");
        }
    }
}
