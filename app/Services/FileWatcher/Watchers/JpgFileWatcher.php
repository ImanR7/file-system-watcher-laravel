<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Intervention\Image\Drivers\Gd\Driver;
use SplFileInfo;
use Intervention\Image\ImageManager;

class JpgFileWatcher implements FileWatcherInterface
{
    protected ImageManager $imageManager;
    protected const WATCHABLE_EXTENSIONS = ['jpg', 'jpeg'];
    protected const SUPPORTED_EVENTS = ['created'];

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function supports(SplFileInfo $file, string $event): bool
    {
        $ext = strtolower($file->getExtension());
        return in_array($ext, self::WATCHABLE_EXTENSIONS) && in_array($event, self::SUPPORTED_EVENTS);
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
        } catch (\Exception $e) {
            logger()->error("JPG Watcher error: " . $e->getMessage());
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
