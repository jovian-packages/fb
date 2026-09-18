<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb;

use Surface\Contracts\Framebuffers\DamageGranularity;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\Framebuffer;
use Surface\Contracts\Framebuffers\FramebufferException;
use Surface\Contracts\Framebuffers\Region;

/** The Surface Framebuffer contract over one ext-fb handle. Bounds are guarded here; the ext never throws. */
class NativeFramebuffer implements Framebuffer
{
    public function __construct(protected Buffer $buffer) {}

    public function buffer(): Buffer { return $this->buffer; }
    public function viewportWidth(): int { return $this->buffer->width(); }
    public function viewportHeight(): int { return $this->buffer->height(); }
    public function hostFormat(): FormatSpec { return $this->buffer->format(); }

    public function getPixel(int $x, int $y): int
    {
        $this->guard($x, $y);

        return $this->buffer->get($x, $y);
    }

    public function setPixel(int $x, int $y, int $value): static
    {
        $this->guard($x, $y);
        $this->buffer->set($x, $y, $value);

        return $this;
    }

    public function setPixels(array $pixels): static
    {
        foreach ($pixels as [$x, $y]) {
            $this->guard($x, $y);
        }
        $this->buffer->setPixels($pixels);

        return $this;
    }

    public function setRegion(array $coordinates, int $value): static
    {
        return $this->setPixels(array_map(fn (array $c) => [$c[0], $c[1], $value], $coordinates));
    }

    public function setSegment(int $x, int $y, int $width, int $height, int $color): static
    {
        $this->buffer->setSegment($x, $y, $width, $height, $color);

        return $this;
    }

    public function clear(): static { return $this->fill(0); }

    public function fill(int $color): static
    {
        $this->buffer->fill($color);

        return $this;
    }

    public function blitTo(Framebuffer $target, int $offset_x = 0, int $offset_y = 0): Framebuffer
    {
        return $target->blitFrom($this, $offset_x, $offset_y);
    }

    public function blitFrom(Framebuffer $source, int $offset_x = 0, int $offset_y = 0): Framebuffer
    {
        $this->buffer->blitRgba8($source->toRgba8(), $source->viewportWidth(), $source->viewportHeight(), $offset_x, $offset_y);

        return $this;
    }

    public function dump(?int $layer = null): string { return $this->buffer->layer($layer); }

    public function flush(FormatSpec $spec, bool $as_array = false): string|array
    {
        return $this->answer($this->flushRegionBytes(Region::wholeSurface($this->viewportWidth(), $this->viewportHeight()), $spec), $as_array);
    }

    public function flushRegion(Region $region, FormatSpec $spec, bool $as_array = false): string|array
    {
        return $this->answer($this->flushRegionBytes($region, $spec), $as_array);
    }

    public function toRgba8(): string { return $this->buffer->toRgba8(); }

    public function damageGranularity(): DamageGranularity
    {
        [$uw, $uh] = $this->buffer->granularity();

        return new DamageGranularity($uw, $uh, $this->viewportWidth(), $this->viewportHeight());
    }

    public function preservesContentsOnPresent(): bool { return true; }

    public function pointer(): int { return $this->buffer->pointer(); }

    protected function flushRegionBytes(Region $r, FormatSpec $spec): string
    {
        return $spec->equals($this->hostFormat())
            ? $this->buffer->region($r->x, $r->y, $r->width, $r->height)
            : $this->buffer->transcode($spec, $r->x, $r->y, $r->width, $r->height);
    }

    protected function guard(int $x, int $y): void
    {
        if ($x < 0 || $y < 0 || $x >= $this->viewportWidth() || $y >= $this->viewportHeight()) {
            throw FramebufferException::outOfRange($x, $y, $this->viewportWidth(), $this->viewportHeight());
        }
    }

    /** @return string|list<int> */
    protected function answer(string $bytes, bool $as_array): string|array
    {
        return $as_array ? array_values(unpack('C*', $bytes)) : $bytes;
    }
}
