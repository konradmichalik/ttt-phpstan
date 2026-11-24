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
final class ManualGlobalsRuleTest extends RuleTestCase
{
    public function testReportsGlobalsSetByHandUnlessAnAttributeCoversThem(): void
    {
        $this->analyse([__DIR__ . '/data/globals.php'], [
            ['$GLOBALS[\'TYPO3_CONF_VARS\'] is set by hand in a test, use #[WithTypo3ConfVars] instead.', 15],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 16],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 52],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 66],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 93],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ManualGlobalsRule(new TestContext(self::createReflectionProvider()));
    }
}
