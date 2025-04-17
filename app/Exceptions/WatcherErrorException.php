<?php

namespace App\Exceptions;

use Exception;

class WatcherErrorException extends Exception
{
    public function __construct(string $filePath, string $message)
    {
        parent::__construct("{$filePath} Watcher error: {$message}");
    }
}
