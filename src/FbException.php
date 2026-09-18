<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb;

use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\FramebufferException;

class FbException extends FramebufferException
{
    public static function refused(string $kind, FormatSpec $spec, int $width, int $height, int $extra): self
    {
        return new self("ext-fb refused a {$kind} buffer: {$spec->pixel_format->value} at {$spec->bit_depth->value} bpp, {$width}x{$height}, extra {$extra}.");
    }

    public static function freed(): self
    {
        return new self('This buffer has been freed.');
    }
}
