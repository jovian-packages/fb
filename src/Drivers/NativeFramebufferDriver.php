<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb\Drivers;

use Jovian\Bindings\Fb\Buffer;
use Jovian\Bindings\Fb\NativeDirtyFramebuffer;
use Jovian\Bindings\Fb\NativeFramebuffer;
use Jovian\Bindings\Fb\NativePagedFramebuffer;
use Jovian\Bindings\Fb\NativeRingFramebuffer;
use Surface\Contracts\Framebuffers\DamageTrackingFramebuffer;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\Framebuffer;
use Surface\Contracts\Framebuffers\FramebufferDriver;
use Surface\Contracts\Framebuffers\FramebufferException;
use Surface\Contracts\Framebuffers\MultiFrameFramebuffer;
use Surface\Contracts\Framebuffers\PagedFramebuffer;

/** Bytes in C. Constructing it without ext-fb loaded is the clear failure the spec asks for. */
final class NativeFramebufferDriver implements FramebufferDriver
{
    public function __construct()
    {
        if (! extension_loaded('fb')) {
            throw FramebufferException::extensionMissing('fb');
        }
    }

    public function driver(): string { return 'native'; }

    public function full(FormatSpec $format, int $width, int $height): Framebuffer
    {
        return new NativeFramebuffer(Buffer::full($format, $width, $height));
    }

    public function dirty(FormatSpec $format, int $width, int $height): DamageTrackingFramebuffer
    {
        return new NativeDirtyFramebuffer(Buffer::dirty($format, $width, $height));
    }

    public function epaper(FormatSpec $format, int $width, int $height): Framebuffer
    {
        return new NativeFramebuffer(Buffer::epaper($format, $width, $height));
    }

    public function paged(FormatSpec $format, int $width, int $height, int $page_rows): PagedFramebuffer
    {
        return new NativePagedFramebuffer(Buffer::paged($format, $width, $height, $page_rows), $page_rows);
    }

    public function ring(FormatSpec $format, int $width, int $height, int $frames): MultiFrameFramebuffer
    {
        return new NativeRingFramebuffer(Buffer::ring($format, $width, $height, $frames));
    }
}
