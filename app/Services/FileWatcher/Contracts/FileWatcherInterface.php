<?php

namespace App\Services\FileWatcher\Contracts;

use SplFileInfo;

interface FileWatcherInterface
{
    public function supports(SplFileInfo $file, string $event): bool;

    public function handle(SplFileInfo $file, string $event): void;
}
