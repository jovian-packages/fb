<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb\Enums;

enum FbKind: int
{
    case FULL = 0;
    case DIRTY = 1;
    case EPAPER = 2;
    case PAGED = 3;
    case RING = 4;
}
