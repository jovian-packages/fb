<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb;

use Surface\Contracts\Framebuffers\Framebuffer;
use Surface\Contracts\Framebuffers\MultiFrameFramebuffer;

final class NativeRingFramebuffer extends NativeFramebuffer implements MultiFrameFramebuffer
{
    public function frames(): int { return $this->buffer->frames(); }
    public function present(): void { $this->buffer->present(); }

    /** The ext reads the front on every read already; the front view is this object. */
    public function front(): Framebuffer { return $this; }

    public function preservesContentsOnPresent(): bool { return false; }
}
