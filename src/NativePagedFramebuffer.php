<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb;

use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\FramebufferException;
use Surface\Contracts\Framebuffers\PagedFramebuffer;
use Surface\Contracts\Framebuffers\Region;

final class NativePagedFramebuffer extends NativeFramebuffer implements PagedFramebuffer
{
    public function __construct(Buffer $buffer, private readonly int $page_rows)
    {
        parent::__construct($buffer);
    }

    public function pageRows(): int { return $this->page_rows; }
    public function pages(): int { return $this->buffer->pages(); }
    public function page(): int { return $this->buffer->page(); }

    public function setPage(int $page): void
    {
        if ($page < 0 || $page >= $this->pages()) {
            throw new FramebufferException("Page {$page} is outside 0..".($this->pages() - 1).'.');
        }
        $this->buffer->setPage($page);
    }

    public function pageRegion(int $page): Region
    {
        $top = $page * $this->page_rows;

        return new Region(0, $top, $this->viewportWidth(), min($this->page_rows, $this->viewportHeight() - $top));
    }

    /** The ext already answers the current page for bytes/region/rgba8; flush() must not ask for the whole surface. */
    public function flush(FormatSpec $spec, bool $as_array = false): string|array
    {
        return $this->flushRegion($this->pageRegion($this->page()), $spec, $as_array);
    }

    public function preservesContentsOnPresent(): bool { return false; }
}
