<?php

declare(strict_types=1);

// The rules only look for these classes by name, so the tests do not need konradmichalik/ttt itself.
// Its PHP range would otherwise limit the test matrix.

namespace KonradMichalik\Ttt\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithTca
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(public string $table, public array $configuration) {}
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithTypo3ConfVars
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(public array $configuration) {}
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithEnvVar
{
    public function __construct(public string $name, public ?string $value = null) {}
}
