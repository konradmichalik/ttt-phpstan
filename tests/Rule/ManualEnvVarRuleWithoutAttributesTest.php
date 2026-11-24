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
final class ManualEnvVarRuleWithoutAttributesTest extends RuleTestCase
{
    public function testReportsNothingWhenTheAttributesAreNotInstalled(): void
    {
        $this->analyse([__DIR__ . '/data/env.php'], []);
    }

    protected function getRule(): Rule
    {
        return new ManualEnvVarRule(new TestContext(self::createReflectionProvider(), 'Missing\\Namespace\\'));
    }
}
