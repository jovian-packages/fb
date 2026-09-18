<?php

declare(strict_types=1);

/*
| jovian/fb tests. Everything but the enum tests needs ext-fb; without it the
| suites skip, and a skipped suite is not evidence — run on the Mac (Herd) or
| the Pi with the ext installed.
*/

require_once __DIR__.'/../vendor/venusian/surface/tests/Support/Framebuffers/FixtureRunner.php';
require_once __DIR__.'/../vendor/venusian/surface/tests/Support/Framebuffers/Rgba8Source.php';

function fbLoaded(): bool
{
    return extension_loaded('fb');
}
