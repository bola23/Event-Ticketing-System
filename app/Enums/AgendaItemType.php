<?php

declare(strict_types=1);

namespace App\Enums;

enum AgendaItemType: string
{
    case Keynote = 'keynote';
    case Session = 'session';
    case WorkshopSession = 'workshop';
    case Break = 'break';
    case Panel = 'panel';
}
