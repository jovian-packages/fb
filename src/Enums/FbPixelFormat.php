<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb\Enums;

use Surface\Contracts\Framebuffers\PixelFormat;

/** ext-fb's pixel_format ints, by Surface's enum. */
enum FbPixelFormat: int
{
    case MONO_VERTICAL_PAGE = 0;
    case MONO_HORIZONTAL = 1;
    case ROW_MAJOR = 2;
    case PLANAR = 3;

    /** Not `from()`: a backed enum's from() is built in and cannot be redeclared. */
    public static function of(PixelFormat $format): self
    {
        return match ($format) {
            PixelFormat::MONO_VERTICAL_PAGE => self::MONO_VERTICAL_PAGE,
            PixelFormat::MONO_HORIZONTAL => self::MONO_HORIZONTAL,
            PixelFormat::ROW_MAJOR => self::ROW_MAJOR,
            PixelFormat::PLANAR => self::PLANAR,
        };
    }
}
