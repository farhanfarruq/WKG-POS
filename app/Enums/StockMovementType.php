<?php

namespace App\Enums;

enum StockMovementType: string
{
    case In         = 'in';
    case Out        = 'out';
    case Adjustment = 'adjustment';
    case Waste      = 'waste';
    case Return     = 'return';
}