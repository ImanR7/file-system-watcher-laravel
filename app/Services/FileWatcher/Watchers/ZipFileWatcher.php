<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use SplFileInfo;
use ZipArchive;


class ZipFileWatcher implements FileWatcherInterface
{
    protected const WATCHABLE_EXTENSION = 'zip';
    protected const SUPPORTED_EVENTS = ['created'];

    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === self::WATCHABLE_EXTENSION && in_array($event, self::SUPPORTED_EVENTS);
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
            logger()->error("Failed to open Zip file: " . $zipPath);
        }
    }
}
