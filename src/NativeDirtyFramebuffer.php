<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb;

use Surface\Contracts\Framebuffers\DamageTrackingFramebuffer;
use Surface\Contracts\Framebuffers\Region;

final class NativeDirtyFramebuffer extends NativeFramebuffer implements DamageTrackingFramebuffer
{
    public function beginEpoch(): void { $this->buffer->beginEpoch(); }

    public function damage(): array
    {
        return array_map(fn (array $r) => new Region($r[0], $r[1], $r[2], $r[3]), $this->buffer->damage());
    }
}
