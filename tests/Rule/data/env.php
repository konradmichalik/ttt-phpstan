<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule\data;

use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    public function setsAndClears(): void
    {
        putenv('MY_EXT_FEATURE=1');
        putenv('MY_EXT_FEATURE');
        \putenv('MY_EXT_OTHER=1');
    }

    public function otherFunctionsAreIgnored(): void
    {
        getenv('MY_EXT_FEATURE');
    }
}

final class NotAnEnvTest
{
    public function run(): void
    {
        putenv('X=1');
    }
}
