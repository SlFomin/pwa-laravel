<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Tests\TestCase;

// Feature, Unit, Laravel — need the Orchestra TestCase (app container, config, etc.)
// Core — pure PHP, no Laravel dependency, intentionally excluded
uses(TestCase::class)->in('Feature', 'Unit', 'Laravel');
