<?php

declare(strict_types=1);

namespace Jovian\Bindings\Fb\Providers;

use Jovian\Bindings\Fb\Drivers\NativeFramebufferDriver;
use Voyager\NutsAndBolts\ServiceProvider;

/** Binds the native driver under the alias Surface's FramebufferManager looks for. Installing this package is the whole enablement. */
class FbServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NativeFramebufferDriver::class);
        $this->app->alias(NativeFramebufferDriver::class, 'framebuffer.native');
    }

    public function boot(): void {}
}
