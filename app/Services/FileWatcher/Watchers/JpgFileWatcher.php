<?php

namespace App\Services\FileWatcher\Watchers;

use App\Enums\SupportedEvents;
use App\Enums\SupportedExtensions;
use App\Exceptions\WatcherErrorException;
use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Intervention\Image\Drivers\Gd\Driver;
use SplFileInfo;
use Intervention\Image\ImageManager;

class JpgFileWatcher implements FileWatcherInterface
{
    protected ImageManager $imageManager;
    protected array $watchableExtensions = [
        SupportedExtensions::JPG->value,
        SupportedExtensions::JPEG->value,
    ];
    protected array $watchableEvents = [
        SupportedEvents::CREATED->value,
    ];

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function supports(SplFileInfo $file, string $event): bool
    {
        $ext = strtolower($file->getExtension());
        return in_array($ext, $this->watchableExtensions) && in_array($event, $this->watchableEvents);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        $path = $file->getRealPath();

        try {
            if ($this->shouldOptimize($path)) {
                $this->optimizeImage($path);
            } else {
                logger()->info("JPG Watcher: No actual change, skipping.");
            }
        } catch (\Throwable $exception) {
            throw new WatcherErrorException($this->watchableExtensions[1], $exception->getMessage());
        }
    }

    private function shouldOptimize(string $path): bool
    {
        $originalHash = md5_file($path);

        $tempPath = $path . '.tmp';
        $this->imageManager
            ->read($path)
            ->save($tempPath, quality: 75);

        $newHash = md5_file($tempPath);
        unlink($tempPath);

        return $originalHash !== $newHash;
    }

    private function optimizeImage(string $path): void
    {
        $this->imageManager
            ->read($path)
            ->save($path, quality: 75);
    }
}
