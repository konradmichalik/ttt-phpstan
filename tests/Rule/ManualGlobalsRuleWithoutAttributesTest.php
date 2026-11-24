<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule;

use KonradMichalik\TttPhpStan\ManualGlobalsRule;
use KonradMichalik\TttPhpStan\TestContext;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ManualGlobalsRule>
 */
final class ManualGlobalsRuleWithoutAttributesTest extends RuleTestCase
{
    public function testReportsNothingWhenTheAttributesAreNotInstalled(): void
    {
        $this->analyse([__DIR__ . '/data/globals.php'], []);
    }

    protected function getRule(): Rule
    {
        return new ManualGlobalsRule(new TestContext(self::createReflectionProvider(), 'Missing\\Namespace\\'));
    }
}
