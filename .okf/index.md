---
okf_version: "0.2"
---

# jovian/fb — knowledge bundle

Typed projection of `ext-fb` and the Surface `native` framebuffer driver. `Jovian\Bindings\Fb\Buffer` owns one handle; `NativeFramebufferDriver` implements `FramebufferDriver` (`driver()` = `'native'`) and `FbServiceProvider` binds it at `framebuffer.native`.

Read this first. Concepts are `status: draft` until a human verifies them.

# Concepts

* [architecture/native-driver.md](/architecture/native-driver.md) - projection, five native kinds, driver, provider, parity, enum ints

# Related bundles

* php-io-extensions/fb — `../../php-io-extensions/fb/.okf/index.md`: C store and `Fb\Buffer`
* venusian/surface — `../../venusian/surface/.okf/index.md`: contracts and the `php` driver this package parities against

# Fast facts

| | |
|---|---|
| Version | 0.8.0, PHP `^8.4\|^8.5\|^8.6` |
| Namespace | `Jovian\Bindings\Fb\` at `src/` |
| Requires | `ext-fb ^0.8.0`, `surface/contracts ^0.8.0`, `venusian-voyager/nuts-and-bolts ^0.8.0` |
| Alias | `framebuffer.native` |
| Tests | 74 on Mac and Pi (`BufferTest` + 27 fixtures + 44 parity) |
