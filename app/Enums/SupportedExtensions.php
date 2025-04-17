<?php

namespace App\Enums;

enum SupportedExtensions: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case JSON = 'json';
    case ZIP = 'zip';
    case TXT = 'txt';
}
