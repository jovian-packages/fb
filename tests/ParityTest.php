<?php

declare(strict_types=1);

use Jovian\Bindings\Fb\Drivers\NativeFramebufferDriver;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\ChannelPalette;
use Surface\Contracts\Framebuffers\ChannelSpec;
use Surface\Contracts\Framebuffers\DamageTrackingFramebuffer;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\Framebuffer;
use Surface\Contracts\Framebuffers\FramebufferDriver;
use Surface\Contracts\Framebuffers\MultiFrameFramebuffer;
use Surface\Contracts\Framebuffers\PagedFramebuffer;
use Surface\Contracts\Framebuffers\PixelFormat;
use Surface\Contracts\Framebuffers\Region;
use Surface\Framebuffers\Php\PhpFramebufferDriver;

/** @return list<FormatSpec> */
function paritySpecs(): array
{
    return [
        new FormatSpec(PixelFormat::MONO_HORIZONTAL, BitDepth::B1),
        new FormatSpec(PixelFormat::MONO_VERTICAL_PAGE, BitDepth::B1),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B2, palette: new ChannelPalette(new ChannelSpec(1, code: 0), new ChannelSpec(0, code: 1), new ChannelSpec(3, code: 2), new ChannelSpec(2, code: 3))),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B4, palette: new ChannelPalette(new ChannelSpec(1, code: 0), new ChannelSpec(0, code: 1), new ChannelSpec(3, code: 2), new ChannelSpec(2, code: 3), new ChannelSpec(4, code: 5), new ChannelSpec(5, code: 6))),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B8),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B12),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B16),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B18),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B24),
        new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B32),
        new FormatSpec(PixelFormat::PLANAR, BitDepth::B1, palette: new ChannelPalette(new ChannelSpec(1, true), new ChannelSpec(2))),
    ];
}

function mint(FramebufferDriver $d, string $kind, FormatSpec $spec, int $w, int $h): Framebuffer
{
    return match ($kind) {
        'full' => $d->full($spec, $w, $h),
        'dirty' => $d->dirty($spec, $w, $h),
        'paged' => $d->paged($spec, $w, $h, 8),
        'ring' => $d->ring($spec, $w, $h, 3),
    };
}

/** One random op applied to both buffers. mt_rand, so the sequence replays from the seed. */
function applyRandom(Framebuffer $a, Framebuffer $b, int $w, int $h, int $maxWord): void
{
    $x = mt_rand(0, $w - 1); $y = mt_rand(0, $h - 1); $v = mt_rand(0, $maxWord);
    switch (mt_rand(0, 6)) {
        case 0: case 1: $a->setPixel($x, $y, $v); $b->setPixel($x, $y, $v); break;
        case 2: $sw = mt_rand(1, $w); $sh = mt_rand(1, $h);
                $a->setSegment($x - 1, $y - 1, $sw, $sh, $v); $b->setSegment($x - 1, $y - 1, $sw, $sh, $v); break;
        case 3: $a->fill($v); $b->fill($v); break;
        case 4: $pts = [[$x, $y, $v], [($x + 1) % $w, $y, $v]]; $a->setPixels($pts); $b->setPixels($pts); break;
        case 5: if ($a instanceof PagedFramebuffer) { $p = mt_rand(0, $a->pages() - 1); $a->setPage($p); $b->setPage($p); } break;
        case 6: if ($a instanceof MultiFrameFramebuffer) { $a->present(); $b->present(); }
                elseif ($a instanceof DamageTrackingFramebuffer) { $a->beginEpoch(); $b->beginEpoch(); } break;
    }
}

it('php and native agree on random op sequences', function (FormatSpec $spec, string $kind) {
    $w = 24; $h = 16;
    $maxWord = match ($spec->bit_depth) { BitDepth::B1 => 1, BitDepth::B2 => 3, BitDepth::B4 => 15, BitDepth::B8 => 255, BitDepth::B12 => 0xFFF, BitDepth::B16 => 0xFFFF, BitDepth::B18, BitDepth::B24 => 0xFFFFFF, BitDepth::B32 => 0xFFFFFFFF };
    if ($spec->pixel_format === PixelFormat::PLANAR) { $maxWord = 3; }
    mt_srand(20260917);
    $php = mint(new PhpFramebufferDriver(), $kind, $spec, $w, $h);
    $native = mint(new NativeFramebufferDriver(), $kind, $spec, $w, $h);

    for ($i = 0; $i < 300; $i++) {
        applyRandom($php, $native, $w, $h, $maxWord);
        if ($i % 25 === 0) {
            expect(bin2hex($native->flush($spec)))->toBe(bin2hex($php->flush($spec)), "flush after op {$i}");
            expect(bin2hex($native->toRgba8()))->toBe(bin2hex($php->toRgba8()), "rgba8 after op {$i}");
            if ($php instanceof DamageTrackingFramebuffer) {
                expect(array_map(fn (Region $r) => [$r->x, $r->y, $r->width, $r->height], $native->damage()))
                    ->toBe(array_map(fn (Region $r) => [$r->x, $r->y, $r->width, $r->height], $php->damage()), "damage after op {$i}");
            }
        }
    }
    $other = new FormatSpec(PixelFormat::ROW_MAJOR, BitDepth::B16);
    expect(bin2hex($native->flush($other)))->toBe(bin2hex($php->flush($other)), 'transcode to 565');
})->with(fn () => array_merge(...array_map(fn (FormatSpec $s) => array_map(fn ($k) => [$s, $k], ['full', 'dirty', 'paged', 'ring']), paritySpecs())))
  ->skip(fn () => ! fbLoaded(), 'ext-fb not loaded');
