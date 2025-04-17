<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use SplFileInfo;
use ZipArchive;


class ZipFileWatcher implements FileWatcherInterface
{
    public function supports(SplFileInfo $file, string $event): bool
    {
        return strtolower($file->getExtension()) === 'zip' && in_array($event, ['created']);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        $path = $file->getRealPath();
        $extractPath = dirname($path) . '/' . pathinfo($path, PATHINFO_FILENAME);

        if (!file_exists($extractPath)) {
            mkdir($extractPath, 0777, true);

            $zip = new ZipArchive();
            if ($zip->open($path) === true) {
                $zip->extractTo($extractPath);
                $zip->close();

                logger()->info("Zip extracted: " . $extractPath);
            } else {
                logger()->error("Failed to open Zip file: " . $path);
            }
        } else {
            logger()->info("Extract directory already exists: " . $extractPath);
        }
    }
}
