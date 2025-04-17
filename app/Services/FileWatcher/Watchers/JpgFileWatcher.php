<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use Intervention\Image\Drivers\Gd\Driver;
use SplFileInfo;
use Intervention\Image\ImageManager;

class JpgFileWatcher implements FileWatcherInterface
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function supports(SplFileInfo $file, string $event): bool
    {
        $ext = strtolower($file->getExtension());
        return in_array($ext, ['jpg', 'jpeg']) && in_array($event, ['created']);
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        $path = $file->getRealPath();

        $originalHash = md5_file($path);

        try {
            $this->imageManager
                ->read($path)
                ->save($path, quality: 75);

            $newHash = md5_file($path);

            if ($originalHash === $newHash) {
                logger()->info("JPG Watcher: No actual change, skipping.");
                return;
            }

        } catch (\Exception $e) {
            logger()->error("JPG Watcher error: " . $e->getMessage());
        }
    }
}
