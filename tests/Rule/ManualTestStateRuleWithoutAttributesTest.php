<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule;

use KonradMichalik\TttPhpStan\ManualTestStateRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ManualTestStateRule>
 */
final class ManualTestStateRuleWithoutAttributesTest extends RuleTestCase
{
    public function testReportsNothingWhenTheAttributesAreNotInstalled(): void
    {
        $this->analyse([__DIR__ . '/data/state.php'], []);
    }

    protected function getRule(): Rule
    {
        return new ManualTestStateRule(self::createReflectionProvider(), 'Missing\\Namespace\\');
    }
}
