<?php

declare(strict_types=1);

use Jovian\Bindings\Fb\Buffer;
use Jovian\Bindings\Fb\Enums\FbKind;
use Jovian\Bindings\Fb\Enums\FbPixelFormat;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\PixelFormat;

it('maps Surface pixel formats to the ext ints', function () {
    expect(FbPixelFormat::of(PixelFormat::MONO_VERTICAL_PAGE)->value)->toBe(0)
        ->and(FbPixelFormat::of(PixelFormat::PLANAR)->value)->toBe(3)
        ->and(FbKind::RING->value)->toBe(4);
});

it('creates and frees a buffer and answers bytes', function () {
    $b = Buffer::full(new FormatSpec(PixelFormat::MONO_HORIZONTAL, BitDepth::B1), 8, 1);
    $b->set(0, 0, 1);

    expect(bin2hex($b->bytes()))->toBe('80')->and($b->pointer())->not->toBe(0);
    $b->free();
    expect($b->handle())->toBe(0);
})->skip(fn () => ! fbLoaded(), 'ext-fb not loaded');

it('a refused create throws', function () {
    expect(fn () => Buffer::ring(new FormatSpec(PixelFormat::MONO_HORIZONTAL, BitDepth::B1), 8, 1, 1))
        ->toThrow(\Jovian\Bindings\Fb\FbException::class);
})->skip(fn () => ! fbLoaded(), 'ext-fb not loaded');
