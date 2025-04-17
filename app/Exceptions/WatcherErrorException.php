<?php

namespace App\Exceptions;

use Exception;

class WatcherErrorException extends Exception
{
    public function __construct(string $extension, string $message)
    {
        parent::__construct("{$extension} Watcher error: {$message}");
    }
}
