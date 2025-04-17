<?php

namespace App\Exceptions;

use Exception;

class InvalidJsonException extends Exception
{
    public function __construct(string $extension)
    {
        parent::__construct("Invalid JSON in file: {$filePath}");
    }
}
