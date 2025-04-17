<?php

namespace App\Enums;

enum SupportedEvents: string
{
    case CREATED = 'created';
    case MODIFIED = 'modified';
    case DELETED = 'deleted';
}
