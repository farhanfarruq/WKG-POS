<?php

namespace App\Enums;

enum KDSStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Recalled   = 'recalled';
}