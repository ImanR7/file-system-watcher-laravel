<?php

namespace App\Exceptions;

use Exception;

class InvalidJsonException extends Exception
{
    public function __construct(string $filePath)
    {
        parent::__construct("Invalid JSON in file: {$filePath}");
    }
}
