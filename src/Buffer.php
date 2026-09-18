<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb;

use Fb\Buffer as Ext;
use Jovian\Bindings\Fb\Enums\FbKind;
use Jovian\Bindings\Fb\Enums\FbPixelFormat;
use Surface\Contracts\Framebuffers\EInkColor;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\FramebufferException;
use Surface\Contracts\Framebuffers\ScanDirection;

/**
 * One ext-fb handle, owned. Every method is one ext call with Surface's
 * enums translated to the ext's ints. free() is idempotent; the destructor
 * calls it. A method on a freed buffer throws.
 */
final class Buffer
{
    private function __construct(private int $handle, private readonly FbKind $kind, private readonly FormatSpec $format, private readonly int $width, private readonly int $height) {}

    public static function full(FormatSpec $f, int $w, int $h): self { return self::mint(FbKind::FULL, $f, $w, $h, 0); }
    public static function dirty(FormatSpec $f, int $w, int $h): self { return self::mint(FbKind::DIRTY, $f, $w, $h, 0); }
    public static function epaper(FormatSpec $f, int $w, int $h): self { return self::mint(FbKind::EPAPER, $f, $w, $h, 0); }
    public static function paged(FormatSpec $f, int $w, int $h, int $page_rows): self { return self::mint(FbKind::PAGED, $f, $w, $h, $page_rows); }
    public static function ring(FormatSpec $f, int $w, int $h, int $frames): self { return self::mint(FbKind::RING, $f, $w, $h, $frames); }

    private static function mint(FbKind $kind, FormatSpec $f, int $w, int $h, int $extra): self
    {
        if (! extension_loaded('fb')) {
            throw FramebufferException::extensionMissing('fb');
        }
        [$pf, $bd, $bo, $en, $pa, $sc, $pal] = self::args($f);
        $handle = Ext::create($kind->value, $pf, $bd, $bo, $en, $pa, $sc, $pal, $w, $h, $extra);
        if ($handle === 0) {
            throw FbException::refused(strtolower($kind->name), $f, $w, $h, $extra);
        }

        return new self($handle, $kind, $f, $w, $h);
    }

    /** @return array{int, int, int, int, int, int, list<array{int, int, int}>} */
    public static function args(FormatSpec $f): array
    {
        $palette = [];
        foreach ($f->palette?->channels ?? [] as $i => $c) {
            $rgb = EInkColor::from($c->color)->color();
            $palette[] = [((int) round($rgb->red * 255) << 16) | ((int) round($rgb->green * 255) << 8) | (int) round($rgb->blue * 255), (int) $c->inverted, $c->code ?? $i];
        }

        return [
            FbPixelFormat::of($f->pixel_format)->value,
            $f->bit_depth->value,
            $f->bit_order?->value ?? -1,
            $f->endianness?->value ?? -1,
            $f->page_axis?->value ?? -1,
            $f->scan_direction === ScanDirection::BOTTOM_TO_TOP ? 1 : 0,
            $palette,
        ];
    }

    public function __destruct()
    {
        $this->free();
    }

    public function free(): void
    {
        if ($this->handle !== 0) {
            Ext::free($this->handle);
            $this->handle = 0;
        }
    }

    public function handle(): int { return $this->handle; }
    public function kind(): FbKind { return $this->kind; }
    public function format(): FormatSpec { return $this->format; }
    public function width(): int { return $this->width; }
    public function height(): int { return $this->height; }

    public function get(int $x, int $y): int { return Ext::get($this->h(), $x, $y); }
    public function set(int $x, int $y, int $v): void { Ext::set($this->h(), $x, $y, $v); }
    /** @param list<array{int, int, int}> $pixels */
    public function setPixels(array $pixels): void { Ext::setPixels($this->h(), $pixels); }
    public function setSegment(int $x, int $y, int $w, int $h, int $v): void { Ext::setSegment($this->h(), $x, $y, $w, $h, $v); }
    public function fill(int $v): void { Ext::fill($this->h(), $v); }
    public function bytes(): string { return Ext::bytes($this->h()); }
    public function region(int $x, int $y, int $w, int $h): string { return Ext::region($this->h(), $x, $y, $w, $h, self::args($this->format)[5]); }
    public function layer(?int $layer): string { return Ext::layer($this->h(), $layer ?? -1); }
    public function pointer(): int { return Ext::pointer($this->h()); }
    public function toRgba8(): string { return Ext::toRgba8($this->h()); }
    public function blitRgba8(string $rgba8, int $sw, int $sh, int $ox, int $oy): void { Ext::blitRgba8($this->h(), $rgba8, $sw, $sh, $ox, $oy); }

    public function transcode(FormatSpec $to, int $x, int $y, int $w, int $h): string
    {
        [$pf, $bd, $bo, $en, $pa, $sc, $pal] = self::args($to);

        return Ext::transcode($this->h(), $pf, $bd, $bo, $en, $pa, $sc, $pal, $x, $y, $w, $h);
    }

    /** @return array{int, int} */
    public function granularity(): array { return Ext::granularity($this->h()); }
    public function beginEpoch(): void { Ext::beginEpoch($this->h()); }
    /** @return list<array{int, int, int, int}> */
    public function damage(): array { return Ext::damage($this->h()); }
    public function setPage(int $page): void { Ext::setPage($this->h(), $page); }
    public function page(): int { return Ext::page($this->h()); }
    public function pages(): int { return Ext::pages($this->h()); }
    public function present(): void { Ext::present($this->h()); }
    public function frames(): int { return Ext::frames($this->h()); }

    private function h(): int
    {
        return $this->handle !== 0 ? $this->handle : throw FbException::freed();
    }
}
