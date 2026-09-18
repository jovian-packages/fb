# jovian/fb

Typed projection of [`ext-fb`](https://github.com/php-io-extensions/fb) and the **native** framebuffer driver for Venusian Surface.

```
ext-fb  →  jovian/fb  →  Surface FramebufferManager
C bytes    Buffer + native driver    framebuffer.native
```

`Fb\Buffer` is ints. This package owns one handle per object, translates Surface enums to those ints, and implements `FramebufferDriver` as `native`.

## Install

Install `ext-fb` 0.8.0 first (`php --ri fb`). Then the two-line enablement:

```bash
composer require jovian/fb
FRAMEBUFFER_DRIVER=native
```

`FbServiceProvider` binds `framebuffer.native`. Installing the package is the whole enablement. Default stays `php` until that env is set.

## Handle lifetime

`Jovian\Bindings\Fb\Buffer` owns one ext handle. `mint()` calls `Fb\Buffer::create`; `0` throws `FbException::refused`. `free()` is idempotent; `__destruct` calls it. A method on a freed handle throws `FbException::freed`. The ext never throws — bounds live in `NativeFramebuffer::guard()`.

## Ext ints

| `FbKind` | Int | `FbPixelFormat` | Int |
|---|---|---|---|
| FULL | 0 | MONO_VERTICAL_PAGE | 0 |
| DIRTY | 1 | MONO_HORIZONTAL | 1 |
| EPAPER | 2 | ROW_MAJOR | 2 |
| PAGED | 3 | PLANAR | 3 |
| RING | 4 | | |

`FbPixelFormat::of(PixelFormat)` — not `from()`.

## Proof

```bash
vendor/bin/pest
```

Needs `ext-fb`. A skipped suite is not evidence. Mac (Herd) and Pi (`fnk`) both ran 74: `BufferTest`, 27 golden fixtures, 44 php/native parity cases.
