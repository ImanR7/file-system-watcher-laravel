<?php

namespace App\Services\FileWatcher\Watchers;

use App\Services\FileWatcher\Contracts\FileWatcherInterface;
use SplFileInfo;
use Illuminate\Support\Facades\Http;

class AntiDeleteMemeWatcher implements FileWatcherInterface
{
    protected $memeApiUrl = 'https://meme-api.com/gimme';

    public function supports(SplFileInfo $file, string $event): bool
    {
        return $event === 'deleted';
    }

    public function handle(SplFileInfo $file, string $event): void
    {
        $path = $file->getRealPath();
        $directory = dirname($path);

        $response = Http::get($this->memeApiUrl);

        if ($response->successful()) {
            $memeImageUrl = $response->json()['url'];

            $imageContent = file_get_contents($memeImageUrl);

            if ($imageContent !== false) {
                $newFileName = pathinfo($file->getFilename(), PATHINFO_FILENAME) . '.jpg';
                $memeImagePath = $directory . '/' . $newFileName;

                if (!file_exists($directory)) {
                    var_dump($directory);
                    mkdir($directory, 0777, true);
                }

                file_put_contents($memeImagePath, $imageContent);
                logger()->info("File replaced with meme: " . $memeImagePath);
            } else {
                logger()->error("Failed to download meme image.");
            }
        } else {
            logger()->error("Failed to fetch meme from API.");
        }
    }
}
