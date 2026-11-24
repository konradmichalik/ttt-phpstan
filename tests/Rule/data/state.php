<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule\data;

use PHPUnit\Framework\TestCase;

final class StateTest extends TestCase
{
    public function nested(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['my_ext'] = [];
        $GLOBALS['TCA']['tt_content']['ctrl']['title'] = 'x';
        putenv('MY_EXT=1');
    }

    public function rootLiteral(): void
    {
        $GLOBALS['TYPO3_CONF_VARS'] = ['A' => 1];
    }

    public function rootNonLiteral(): void
    {
        $GLOBALS['TYPO3_CONF_VARS'] = 'not-an-array';
    }

    public function otherKeysAreIgnored(): void
    {
        $GLOBALS['BE_USER'] = null;
        $GLOBALS['LANG'] = null;
        $other = [];
        $other['TCA']['x'] = 1;
    }
}

final class NotATest
{
    public function run(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
        putenv('X=1');
    }
}

#[\KonradMichalik\Ttt\Attribute\WithTypo3ConfVars(['A' => 1])]
final class ClassSandboxedTest extends TestCase
{
    public function confVarsCovered(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['A']['b'] = 1;
    }

    public function tcaNotCovered(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
    }
}

final class MethodSandboxedTest extends TestCase
{
    #[\KonradMichalik\Ttt\Attribute\WithTca(['x' => []])]
    public function tcaCovered(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
    }

    public function tcaOtherMethod(): void
    {
        $GLOBALS['TCA']['x']['y'] = 2;
    }
}
