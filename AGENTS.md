# Agent guidelines — jovian/fb

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from the Composer dist). Read [`.okf/index.md`](.okf/index.md) first; update the affected concept and append [`.okf/log.md`](.okf/log.md) when you learn something durable; new or changed concepts stay `status: draft`.

## Where this package sits

`ext-fb` (opinionated C buffers, `Fb\Buffer` handles) → **`jovian/fb`** (typed projection + `native` driver) → `venusian/surface` (`FramebufferManager` at `framebuffer.native`).

## Rules

- Do **not** read `venusian/surface/src/Surface/Framebuffers`. Code to `Surface\Contracts\Framebuffers\*`, `FixtureRunner`, and `Fb\Buffer`. Parity uses `PhpFramebufferDriver` as a black box only.
- A php/native disagreement is a packings-table question. Re-derive. Never make one driver match the other.
- `declare(strict_types=1)`. Enums int-backed, FULLY UPPERCASE cases. No class constants. Prefer `is_null()`.
- `FbPixelFormat::of()` — a backed enum cannot redeclare `from()`.
- Bounds and refusals belong here. The ext never throws; `create` answering `0` is `FbException::refused`.
- Provider alias is `framebuffer.native`. Do not rename it.
- `paged` `flush()` uses `pageRegion($this->page())`, not the whole surface. Ring `front()` is `$this`.
- Mac `composer update` is fine. Do not run Composer against an SBC mount; Pi work goes through Angel/`fnk`.

## Verification

```bash
vendor/bin/pest
```

Expected: `BufferTest`, all 27 fixtures on native, 44 parity cases. Skip without `ext-fb` is not evidence.
