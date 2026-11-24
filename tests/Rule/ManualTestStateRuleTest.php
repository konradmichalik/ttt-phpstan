<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule;

use KonradMichalik\TttPhpStan\ManualTestStateRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ManualTestStateRule>
 */
final class ManualTestStateRuleTest extends RuleTestCase
{
    public function testReportsManualStateInTestCases(): void
    {
        $this->analyse([__DIR__ . '/data/state.php'], [
            ['$GLOBALS[\'TYPO3_CONF_VARS\'] is set by hand in a test, use #[WithTypo3ConfVars] instead.', 13],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 14],
            ['Environment variable set with putenv() in a test, use #[WithEnvVar] instead.', 15],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 56],
            ['$GLOBALS[\'TCA\'] is set by hand in a test, use #[WithTca] instead.', 70],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ManualTestStateRule(self::createReflectionProvider());
    }
}
