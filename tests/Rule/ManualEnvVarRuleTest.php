<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule;

use KonradMichalik\TttPhpStan\ManualEnvVarRule;
use KonradMichalik\TttPhpStan\TestContext;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ManualEnvVarRule>
 */
final class ManualEnvVarRuleTest extends RuleTestCase
{
    public function testReportsPutenvInTestCases(): void
    {
        $message = 'Environment variable set or cleared with putenv() in a test, use #[WithEnvVar] instead.';

        $this->analyse([__DIR__ . '/data/env.php'], [
            [$message, 13],
            [$message, 14],
            [$message, 15],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ManualEnvVarRule(new TestContext(self::createReflectionProvider()));
    }
}
