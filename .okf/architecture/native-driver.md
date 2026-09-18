---
type: Architecture
title: Native framebuffer driver
description: ext-fb projection, five Native* kinds, framebuffer.native, and the php/native parity suite
tags: [fb, framebuffer, native, jovian, parity]
status: draft
generated: { by: cursor-grok-4.6/1, at: "2026-09-18T00:30:00Z" }
sources:
  - id: plan-t6
    resource: venusian/surface/docs/superpowers/plans/2026-09-17-cpu-rendering.md
    title: CPU rendering plan Task 6
  - id: spec-s2
    resource: venusian/surface/docs/superpowers/specs/2026-09-17-cpu-rendering-design.md
    title: CPU rendering spec §2 packings
---

# Overview

`jovian/fb` is both the typed projection of `ext-fb` and Surface's `native` driver. Installing the package is the enablement: `FbServiceProvider` binds `NativeFramebufferDriver` at `framebuffer.native`. `FRAMEBUFFER_DRIVER=native` is how Surface elects it.[^plan-t6]

# Projection

`Jovian\Bindings\Fb\Buffer` wraps one `Fb\Buffer` handle. `mint()` calls `Fb\Buffer::create`; `0` throws `FbException::refused`. `free()` is idempotent; the destructor calls it. Methods on a freed handle throw `FbException::freed`. `args()` encodes each palette row as `[rgb24, inverted, code]`.

The ext never throws. Bounds live in `NativeFramebuffer::guard()`.

# Enum ints

`FbKind`: FULL=0 DIRTY=1 EPAPER=2 PAGED=3 RING=4.

`FbPixelFormat`: MONO_VERTICAL_PAGE=0 MONO_HORIZONTAL=1 ROW_MAJOR=2 PLANAR=3. Use `of(PixelFormat)`, not `from()`.

# Driver

`NativeFramebufferDriver` (`driver()` = `'native'`) mints:

| Kind | Class | Contract extras |
|---|---|---|
| full / epaper | `NativeFramebuffer` | bounds, flush/region/rgba8, blit via `blitRgba8` |
| dirty | `NativeDirtyFramebuffer` | `beginEpoch()`, `damage()` as `Region`s |
| paged | `NativePagedFramebuffer` | `flush()` = `pageRegion($this->page())`; `preservesContentsOnPresent` false |
| ring | `NativeRingFramebuffer` | `front()` is `$this`; `preservesContentsOnPresent` false |

Constructor throws `FramebufferException::extensionMissing('fb')` if the ext is absent.

# Parity

`tests/ParityTest.php`: 11 specs × `{full,dirty,paged,ring}` = 44 cases. Seed `20260917`, 300 `applyRandom` ops, every 25th op compares flush hex, rgba8 hex, and dirty `Region` tuples; then both transcode to ROW_MAJOR B16. Fixtures (27) run through `FixtureRunner` on the native driver.

Neither driver is the reference. The packings table is. Planar unmap of a multi-bit mask is the lowest set bit's colour; `0` is paper. PHP `PixelMapper::unmap` must do the same or parity fails on PLANAR when a random op writes word `3`.

# Proof

Mac (Herd) and Pi (`fnk`, path repos, no `vendor/` on the push): 74 passed, 1393 assertions.

[^plan-t6]: Task 6 listing in the CPU rendering plan
