<?php

declare(strict_types=1);

use Jovian\Bindings\Fb\Drivers\NativeFramebufferDriver;
use Venusian\Surface\Tests\Support\Framebuffers\FixtureRunner;

it('passes every golden fixture on the native driver', function (string $file) {
    FixtureRunner::run(new NativeFramebufferDriver(), $file);
})->with(fn () => array_combine(array_map('basename', FixtureRunner::files()), FixtureRunner::files()))
  ->skip(fn () => ! fbLoaded(), 'ext-fb not loaded');
